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

    /**
     * @param User[] $users
     */
    #[DataProvider('findUsersToIndexProvider')]
    public function testFindUsersToIndex(int $page, array $users): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(
                [], 
                ['id' => 'ASC'],
                25,
                25 * ($page - 1)
            )
            ->willReturn($users);

        self::assertSame($users, $this->userService->findUsersToIndex($page));
    }

    public function testUsersCountExcludesTheAdminUser(): void
    {
        $this->userRepository->expects(self::once())->method('count')->willReturn(4);

        self::assertSame(4, $this->userService->usersCount());
    }

    #[DataProvider('addUserProvider')]
    public function testAddUserHashesPasswordAndPersistsUser(string $password, string $hashedPassword): void
    {
        $user = (new User())->setPassword($password);

        $this->hasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with($user, $password)
            ->willReturn($hashedPassword);
        $this->entityManager->expects(self::once())->method('persist')->with($user);
        $this->entityManager->expects(self::once())->method('flush');

        $this->userService->addUser($user);

        self::assertSame($hashedPassword, $user->getPassword());
        self::assertSame(['ROLE_ACTIVE_USER'], $user->getRoles());
    }

    #[DataProvider('updateUserProvider')]
    public function testUpdateUser(string $newPassword, string $hashedPassword) : void
    {
        $user = (new User())
            ->setPassword($newPassword);

        $this->hasher->expects(self::once())
            ->method('hashPassword')
            ->willReturn($hashedPassword)
        ;
        $this->entityManager->expects(self::once())->method('persist')->with($user);
        $this->entityManager->expects(self::once())->method('flush');

        $user->setPassword($newPassword);
        $this->userService->updateUser($user, 'old-password');

        self::assertSame($hashedPassword, $user->getPassword());
    }

    #[DataProvider('updateUserProviderKeep')]
    public function testUpdateUserKeepsExistingPasswordWhenNewPasswordIsEmpty(string $newPassword, string $oldPassword, string $expectedPassword): void
    {
        $user = (new User())
            ->setPassword($oldPassword);

        $this->hasher->expects(self::never())->method('hashPassword');
        $this->entityManager->expects(self::once())->method('persist')->with($user);
        $this->entityManager->expects(self::once())->method('flush');

        $user->setPassword($newPassword);
        $this->userService->updateUser($user, $oldPassword);

        self::assertSame($expectedPassword, $user->getPassword());
    }

    public function testDeleteUserDataDeletesUserMediaWithoutFlushingThenDeletesUser(): void
    {
        $user = new User();
        $media = new Media();
        $user->setMedias(new ArrayCollection([$media]));

        $this->mediaService
            ->expects(self::once())
            ->method('deleteMedia')
            ->with($media, false);
        $this->entityManager->expects(self::once())->method('remove')->with($user);
        $this->entityManager->expects(self::once())->method('flush');

        $this->userService->deleteUserData($user);
    }

    /**
     * @param string[] $roles
     */
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
            ->expects($shouldDeactivate ? self::once() : self::never())
            ->method('persist')
            ->with($user);
        $this->entityManager
            ->expects($shouldDeactivate ? self::once() : self::never())
            ->method('flush');

        $this->userService->deactivateUser($user);

        self::assertSame($shouldHaveActiveRoleAfterward, $user->hasRole('ROLE_ACTIVE_USER'));
    }

    /**
     * @param string[] $roles
     */
    #[DataProvider('activateUserProvider')]
    public function testActivateUserOnlyChangesUsersWithoutActiveRole(array $roles, bool $shouldActivate): void
    {
        $user = new User();
        $user->setRoles($roles);

        $this->entityManager
            ->expects($shouldActivate ? self::once() : self::never())
            ->method('persist')
            ->with($user);
        $this->entityManager
            ->expects($shouldActivate ? self::once() : self::never())
            ->method('flush');

        $this->userService->activateUser($user);

        self::assertTrue($user->hasRole('ROLE_ACTIVE_USER'));
    }

    #endregion

    #region DataProviders

    /**
     * @return array<string, array{int, User[]}>
     */
    public static function findUsersToIndexProvider(): array
    {
        return [
            'first page' => [1, []],
            'later page' => [3, [new User()]],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function addUserProvider(): array
    {
        return [
            'plain password' => ['password', 'hashed-password'],
            'another password' => ['another-password', 'another-hash'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function updateUserProvider(): array
    {
        return [
            'password_change' => ['password', 'hash-password'],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function updateUserProviderKeep(): array
    {
        return [
            'password_keep' => ['', 'old-hash', 'old-hash'],
        ];
    }

    /**
     * @return array<string, array{string[], bool, bool}>
     */
    public static function deactivateUserProvider(): array
    {
        return [
            'active user' => [['ROLE_ACTIVE_USER'], true, false],
            'inactive user' => [['ROLE_USER'], false, false],
            'active admin' => [['ROLE_ACTIVE_USER', 'ROLE_ADMIN'], false, true],
        ];
    }

    /**
     * @return array<string, array{string[], bool}>
     */
    public static function activateUserProvider(): array
    {
        return [
            'inactive user' => [['ROLE_USER'], true],
            'active user' => [['ROLE_ACTIVE_USER'], false],
        ];
    }

    #endregion
}
