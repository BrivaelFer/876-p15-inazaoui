<?php

namespace App\Tests\Unit;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    #[DataProvider('defaultRolesProvider')]
    public function testDefaultRolesAreReturnedWhenNoRolesAreSet(array $expectedRoles): void
    {
        $user = new User();

        self::assertSame($expectedRoles, $user->getRoles());
    }

    public function testNewUserValues(): void
    {
        $user = new User();

        self::assertNull($user->getId());
        self::assertNull($user->getEmail());
        self::assertNull($user->getName());
        self::assertNull($user->getDescription());
        self::assertNull($user->getPassword());
    }

    #[DataProvider('profileProvider')]
    public function testProfileValuesCanBeSetAndReadBack(string $email, ?string $name, ?string $description): void
    {
        $user = new User();

        $user->setEmail($email);
        $user->setName($name);
        $user->setDescription($description);

        self::assertSame($email, $user->getEmail());
        self::assertSame($name, $user->getName());
        self::assertSame($description, $user->getDescription());
    }

    #[DataProvider('mediasProvider')]
    public function testMediasCanBeSetAndReadBack(ArrayCollection $medias): void
    {
        $user = new User();
        $medias = new ArrayCollection([new Media()]);

        $user->setMedias($medias);

        self::assertSame($medias, $user->getMedias());
    }

    #[DataProvider('addRoleProvider')]
    public function testAddRoleWorksWhenNoRolesAreSet(string $role, array $expectedRoles): void
    {
        $user = new User();

        $user->addRole($role);

        self::assertSame($expectedRoles, $user->getRoles());
    }

    #[DataProvider('passwordProvider')]
    public function testPasswordCanBeSetAndReadBack(string $password): void
    {
        $user = new User();

        $user->setPassword($password);

        self::assertSame($password, $user->getPassword());
    }

    #[DataProvider('identifierProvider')]
    public function testUserIdentifierReturnsTheEmailAsAString(string $email): void
    {
        $user = new User();
        $user->setEmail($email);

        self::assertSame($email, $user->getUserIdentifier());
    }

    #[DataProvider('removeRoleProvider')]
    public function testRemoveRoleKeepsTheOtherRoles(array $roles, string $roleToRemove, array $expectedRoles): void
    {
        $user = new User();
        $user->setRoles($roles);

        $user->removeRole($roleToRemove);

        self::assertCount(count($expectedRoles), $user->getRoles());
        foreach ($expectedRoles as $role) {
            self::assertContains($role, $user->getRoles());
        }
    }

    #[DataProvider('hasRoleProvider')]
    public function testHasRoleChecksTheRoleList(array $roles, string $role, bool $expectedResult): void
    {
        $user = new User();
        $user->setRoles($roles);

        self::assertSame($expectedResult, $user->hasRole($role));
    }

    public static function removeRoleProvider(): array
    {
        return [
            'removes active role' => [
                ['ROLE_USER', 'ROLE_ACTIVE_USER', 'ROLE_ADMIN'],
                'ROLE_ACTIVE_USER',
                ['ROLE_USER', 'ROLE_ADMIN'],
            ],
        ];
    }

    public static function defaultRolesProvider(): array
    {
        return [
            'default user role' => [['ROLE_USER']],
        ];
    }

    public static function addRoleProvider(): array
    {
        return [
            'active user role' => ['ROLE_ACTIVE_USER', ['ROLE_ACTIVE_USER']],
        ];
    }

    public static function mediasProvider(): array
    {
        return [
            'one media' => [new ArrayCollection([new Media()])],
        ];
    }

    public static function profileProvider(): array
    {
        return [
            'complete profile' => ['user@example.com', 'Jane Doe', 'Photographer'],
            'nullable profile values' => ['other@example.com', null, null],
        ];
    }

    public static function passwordProvider(): array
    {
        return [
            'short password' => ['secret'],
            'hashed password' => ['$2y$hashed-password'],
        ];
    }

    public static function identifierProvider(): array
    {
        return [
            'user email' => ['user@example.com'],
        ];
    }

    public static function hasRoleProvider(): array
    {
        return [
            'existing role' => [['ROLE_ADMIN', 'ROLE_ACTIVE_USER'], 'ROLE_ACTIVE_USER', true],
            'unknown role' => [['ROLE_ADMIN', 'ROLE_ACTIVE_USER'], 'ROLE_UNKNOWN', false],
        ];
    }
}
