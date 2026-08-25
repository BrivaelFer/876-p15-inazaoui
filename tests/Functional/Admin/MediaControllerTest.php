<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class MediaControllerTest extends WebTestCase
{
    private const EMAIL_PREFIX = 'functional-media-controller-';
    private const TITLE_PREFIX = 'Functional media controller ';

    private EntityManagerInterface $entityManager;
    private string $projectDir;
    private string $initialWorkingDirectory;
    private User $admin;
    private User $nonAdmin;
    private User $nonActiveUser;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $this->initialWorkingDirectory = getcwd();
        chdir($this->projectDir.'/public');

        $this->admin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);
        $this->nonAdmin = $this->createUser('active', ['ROLE_ACTIVE_USER']);
        $this->nonActiveUser = $this->createUser('non-active', []);
    }

    protected function tearDown(): void
    {
        foreach ($this->entityManager->getRepository(Media::class)->findAll() as $media) {
            if (str_starts_with($media->getTitle(), self::TITLE_PREFIX)) {
                $this->removeMediaFile($media);
                $this->entityManager->remove($media);
            }
        }

        foreach ($this->entityManager->getRepository(Album::class)->findAll() as $album) {
            if ($album->getName() === 'Functional album') {
                $this->entityManager->remove($album);
            }
        }

        foreach ($this->entityManager->getRepository(User::class)->findAll() as $user) {
            if (str_starts_with((string) $user->getEmail(), self::EMAIL_PREFIX)) {
                $this->entityManager->remove($user);
            }
        }

        $this->entityManager->flush();
        chdir($this->initialWorkingDirectory);
        parent::tearDown();
    }

    public function testActiveUserIndexDisplay(): void
    {
        $path = '/admin/media';
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 2]);
        $this->client->loginUser($user);
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();

        self::assertSelectorCount(3, 'th');
        self::assertSelectorCount(26, 'tr');
        self::assertSelectorCount(4, '.page-item');

        self::assertAnySelectorTextContains('thead', 'Image');
        self::assertAnySelectorTextContains('thead', 'Titre');
        self::assertAnySelectorTextContains('thead', 'Action');
    }

    public function testAdminIndexDisplay(): void
    {
        $path = '/admin/media';
        $this->client->loginUser($this->admin);
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();

        self::assertSelectorCount(5, 'th');
        self::assertSelectorCount(26, 'tr');

        self::assertAnySelectorTextContains('thead', 'Image');
        self::assertAnySelectorTextContains('thead', 'Titre');
        self::assertAnySelectorTextContains('thead', 'Action');
        self::assertAnySelectorTextContains('thead', 'Artiste');
        self::assertAnySelectorTextContains('thead', 'Album');
    }

    public function testActiveUserAddDisplay(): void
    {
        $path = '/admin/media/add';
        $this->client->loginUser($this->nonAdmin);
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('#media_title');
        self::assertSelectorExists('#media_file');

        self::assertSelectorNotExists('#media_user');
        self::assertSelectorNotExists('#media_album');
    }

    public function testAdminAddDisplay(): void
    {
        $path = '/admin/media/add';
        $this->client->loginUser($this->admin);
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('#media_title');
        self::assertSelectorExists('#media_file');
        self::assertSelectorExists('#media_user');
        self::assertSelectorExists('#media_album');
    }

    public function testActiveUserCanSubmitAddForm(): void
    {
        $title = self::TITLE_PREFIX.'added';

        $this->client->loginUser($this->nonAdmin);
        $this->client->request('GET', '/admin/media/add');

        $form = $this->client->getCrawler()->selectButton('Ajouter')->form([
            'media[title]' => $title,
        ]);
        $form['media[file]'] = $this->uploadedImage();

        $this->client->submit($form);

        self::assertResponseRedirects('/admin/media');

        $media = $this->entityManager->getRepository(Media::class)->findOneBy([
            'title' => $title,
        ]);

        self::assertNotNull($media);
        self::assertSame($this->nonAdmin->getId(), $media->getUser()->getId());
        self::assertStringStartsWith('uploads-test/', $media->getPath());
        self::assertFileExists($this->projectDir.'/public/'.$media->getPath());
    }

    public function testAdminCanSubmitAddForm(): void
    {
        $title = self::TITLE_PREFIX.'added';
        $album = $this->createAlbum('_submitAdmin');

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/media/add');

        $form = $this->client->getCrawler()->selectButton('Ajouter')->form([
            'media[title]' => $title,
            'media[user]' => $this->admin->getId(),
            'media[album]' => $album->getId()
        ]);
        $form['media[file]'] = $this->uploadedImage();

        $this->client->submit($form);

        self::assertResponseRedirects('/admin/media');

        $media = $this->entityManager->getRepository(Media::class)->findOneBy([
            'title' => $title,
        ]);

        self::assertNotNull($media);
        self::assertSame($this->admin->getId(), $media->getUser()->getId());
        self::assertStringStartsWith('uploads-test/', $media->getPath());
        self::assertFileExists($this->projectDir.'/public/'.$media->getPath());
    }

    #[DataProvider('deleteMediaProvider')]
    public function testDeleteMedia(string $loggedUser, string $mediaOwner, bool $shouldBeDeleted): void
    {
        $media = $this->createMedia('delete-test', $this->{$mediaOwner});
        $mediaId = $media->getId();
        $mediaPath = $this->projectDir.'/public/'.$media->getPath();

        $this->client->loginUser($this->{$loggedUser});
        $this->client->request('GET', sprintf('/admin/media/delete/%d', $mediaId));

        self::assertResponseRedirects('/admin/media');

        if ($shouldBeDeleted) {
            self::assertNull($this->entityManager->getRepository(Media::class)->find($mediaId));
            self::assertFileDoesNotExist($mediaPath);
        } else {
            self::assertNotNull($this->entityManager->getRepository(Media::class)->find($mediaId));
            self::assertFileExists($mediaPath);
        }
    }

    public static function deleteMediaProvider(): array
    {
        return [
            'owner can delete media' => ['nonAdmin', 'nonAdmin', true],
            'admin can delete media' => ['admin', 'nonAdmin', true],
            'other user cannot delete media' => ['nonAdmin', 'admin', false],
        ];
    }

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

    private function createAlbum(string $name): Album
    {
        $album = new Album();
        $album->setName($name);
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        return $album;
    }

    private function createMedia(string $suffix, User $user): Media
    {
        $media = new Media();
        $media->setTitle(self::TITLE_PREFIX.$suffix);
        $media->setUser($user);
        $media->setPath('uploads-test/'.uniqid('', true).'.jpg');
        copy($this->projectDir.'/src/DataFixtures/Assets/img/fix-1.jpg', $this->projectDir.'/public/'.$media->getPath());

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }

    private function uploadedImage(): UploadedFile
    {
        return new UploadedFile(
            $this->projectDir.'/src/DataFixtures/Assets/img/fix-1.jpg',
            'fix-1.jpg',
            'image/jpeg',
            null,
            true
        );
    }

    private function removeMediaFile(Media $media): void
    {
        $path = $this->projectDir.'/public/'.$media->getPath();
        if (is_file($path)) {
            unlink($path);
        }
    }
}
