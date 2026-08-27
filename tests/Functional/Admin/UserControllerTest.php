<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private const EMAIL_PREFIX = 'functional-user-controller-';

    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;
    private User $admin;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->admin = $this->createUser('admin', ['ROLE_ADMIN']);
        $this->client->loginUser($this->admin);
    }

    protected function tearDown(): void
    {
        foreach ($this->entityManager->getRepository(User::class)->findAll() as $user) {
            if (str_starts_with((string) $user->getEmail(), self::EMAIL_PREFIX)) {
                $this->entityManager->remove($user);
            }
        }

        $this->entityManager->flush();
        parent::tearDown();
    }

    public function testIndexDisplaysUsers(): void
    {
        $user = $this->createUser('index', ['ROLE_ACTIVE_USER']);

        $users = $this->entityManager->getRepository(User::class)->findAll();
        $userPosition = array_search($user->getId(), array_map(
            static fn (User $listedUser): ?int => $listedUser->getId(),
            $users
        ), true);
        $page = intdiv((int) $userPosition, 25) + 1;
        $this->client->request('GET', '/admin/user?page='.$page);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main h1', 'Invités');
        self::assertSelectorTextContains('tbody', (string) $user->getEmail());
    }

    #[DataProvider('userControllerRoutesProvider')]
    public function testNonAdminCannotAccessUserController(string $route, bool $requiresUser): void
    {
        if ($requiresUser) {
            $targetUser = $this->createUser('target', ['ROLE_ACTIVE_USER']);
            $route = sprintf($route, $targetUser->getId());
        }

        $nonAdmin = $this->createUser('non-admin', ['ROLE_ACTIVE_USER']);
        $this->client->loginUser($nonAdmin);

        $this->client->request('GET', $route);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2.home-title', 'Photographe');
    }

    

    #[DataProvider('addUserWithValidDataRedirectsToIndexDataProvider')]
    public function testAddUserWithValidDataRedirectsToIndex(
        string $email, 
        string $name, 
        string $description, 
        string $password
    ): void {
        $this->client->request('GET', '/admin/user/add');
        $form = $this->client->getCrawler()->selectButton('Ajouter')->form([
            'add_user[email]' => $email,
            'add_user[name]' => $name,
            'add_user[description]' => $description,
            'add_user[password]' => $password,
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/admin/user');
        self::assertNotNull($this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]));
    }

    

    /**
     * @param array<string> $initialRoles
     */
    #[DataProvider('userStatusProvider')]
    public function testUserStatusAction(
        string $action,
        array $initialRoles,
        bool $expectedActive
    ): void
    {
        $user = $this->createUser($action, $initialRoles);

        $this->client->request('GET', sprintf('/admin/user/%d/%s', $user->getId(), $action));

        self::assertResponseRedirects('/admin/user');
        $this->entityManager->clear();
        $updatedUser = $this->entityManager->getRepository(User::class)->find($user->getId());

        self::assertSame($expectedActive, $updatedUser->hasRole('ROLE_ACTIVE_USER'));
    }

   
    /**
     * @param array<string> $initialRoles
     */
    #[DataProvider('deleteUserRedirectsToIndexAndRemovesUserDataProvider')]
    public function testDeleteUserRedirectsToIndexAndRemovesUser(string $suffix, array $initialRoles): void
    {
        $user = $this->createUser($suffix, $initialRoles);
        $userId = $user->getId();

        $this->client->request('GET', sprintf('/admin/user/%d/delete', $userId));

        self::assertResponseRedirects('/admin/user');
        self::assertNull($this->entityManager->getRepository(User::class)->find($userId));
    }

    

    /**
     * @param array<string> $roles
     */
    private function createUser(string $suffix, array $roles): User
    {
        $user = new User();
        $user->setName('Utilisateur '.$suffix);
        $user->setEmail(self::EMAIL_PREFIX.$suffix.'@example.com');
        $user->setPassword('password');
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function userControllerRoutesProvider(): array
    {
        return [
            'index' => ['/admin/user', false],
            'add' => ['/admin/user/add', false],
            'delete' => ['/admin/user/%d/delete', true],
            'deactivate' => ['/admin/user/%d/deactivate', true],
            'activate' => ['/admin/user/%d/activate', true],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public static function addUserWithValidDataRedirectsToIndexDataProvider(): array
    {
        return [
            'add user' => [
                self::EMAIL_PREFIX.'added@example.com',
                'Utilisateur ajoute',
                'Description de test',
                'password',
            ]
        ];
    }

    /**
     * @return array<string, array{string, array<string>, bool}>
     */
    public static function userStatusProvider(): array
    {
        return [
            'deactivate active user' => ['deactivate', ['ROLE_ACTIVE_USER'], false],
            'activate inactive user' => ['activate', ['ROLE_USER'], true],
        ];
    }
    
    /**
     * @return array<string, array{string, array<string>}>
     */
    public static function deleteUserRedirectsToIndexAndRemovesUserDataProvider(): array
    {
        return [
            'remove and redirect' => ['deleted', ['ROLE_ACTIVE_USER']]
        ];
    }
}