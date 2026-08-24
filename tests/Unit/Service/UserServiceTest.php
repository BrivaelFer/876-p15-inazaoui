<?php

namespace App\Tests\Unit;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MediaService;
use App\Service\UserService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UserRepository&MockObject $userRepository;
    private UserPasswordHasherInterface&MockObject $hasher;
    private MediaService&MockObject $mediaService;
    private UserService $userService;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->mediaService = $this->createMock(MediaService::class);
        $this->userService = new UserService(
            $this->entityManager,
            $this->userRepository,
            $this->hasher,
            $this->mediaService
        );
    }

    #region Tests

    #[DataProvider('findUsersToIndexProvider')]
    public function testFindUsersToIndex(int $page, array $users): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findActiveUsers')
            ->with($page, 25)
            ->willReturn($users);

        self::assertSame($users, $this->userService->findUsersToIndex($page));
    }

    public function testUsersCountExcludesTheAdminUser(): void
    {
        $this->userRepository->expects($this->once())->method('count')->willReturn(4);

        self::assertSame(3, $this->userService->usersCount());
    }

    #[DataProvider('addUserProvider')]
    public function testAddUserHashesPasswordAndPersistsUser(string $password, string $hashedPassword): void
    {
        $user = (new User())->setPassword($password);

        $this->hasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, $password)
            ->willReturn($hashedPassword);
        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->addUser($user);

        self::assertSame($hashedPassword, $user->getPassword());
        self::assertSame(['ROLE_ACTIVE_USER'], $user->getRoles());
    }

    public function testDeleteUserDataDeletesUserMediaWithoutFlushingThenDeletesUser(): void
    {
        $user = new User();
        $media = $this->createMock(Media::class);
        $user->setMedias(new ArrayCollection([$media]));

        $this->mediaService
            ->expects($this->once())
            ->method('deleteMedia')
            ->with($media, false);
        $this->entityManager->expects($this->once())->method('remove')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->deleteUserData($user);
    }

    #[DataProvider('deactivateUserProvider')]
    public function testDeactivateUserOnlyChangesActiveNonAdminUsers(
        array $roles,
        bool $shouldDeactivate,
        bool $shouldHaveActiveRoleAfterward
    ): void
    {
        $user = new User();
        $user->setRoles($roles);

        $this->entityManager
            ->expects($shouldDeactivate ? $this->once() : $this->never())
            ->method('persist')
            ->with($user);
        $this->entityManager
            ->expects($shouldDeactivate ? $this->once() : $this->never())
            ->method('flush');

        $this->userService->deactivateUser($user);

        self::assertSame($shouldHaveActiveRoleAfterward, $user->hasRole('ROLE_ACTIVE_USER'));
    }

    #[DataProvider('activateUserProvider')]
    public function testActivateUserOnlyChangesUsersWithoutActiveRole(array $roles, bool $shouldActivate): void
    {
        $user = new User();
        $user->setRoles($roles);

        $this->entityManager
            ->expects($shouldActivate ? $this->once() : $this->never())
            ->method('persist')
            ->with($user);
        $this->entityManager
            ->expects($shouldActivate ? $this->once() : $this->never())
            ->method('flush');

        $this->userService->activateUser($user);

        self::assertTrue($user->hasRole('ROLE_ACTIVE_USER'));
    }

    #endregion

    #region DataProviders

    public static function findUsersToIndexProvider(): array
    {
        return [
            'first page' => [1, []],
            'later page' => [3, [new User()]],
        ];
    }

    public static function addUserProvider(): array
    {
        return [
            'plain password' => ['password', 'hashed-password'],
            'another password' => ['another-password', 'another-hash'],
        ];
    }

    public static function deactivateUserProvider(): array
    {
        return [
            'active user' => [['ROLE_ACTIVE_USER'], true, false],
            'inactive user' => [['ROLE_USER'], false, false],
            'active admin' => [['ROLE_ACTIVE_USER', 'ROLE_ADMIN'], false, true],
        ];
    }

    public static function activateUserProvider(): array
    {
        return [
            'inactive user' => [['ROLE_USER'], true],
            'active user' => [['ROLE_ACTIVE_USER'], false],
        ];
    }

    #endregion
}
