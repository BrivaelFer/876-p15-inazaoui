<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Album;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class AlbumControllerTest extends WebTestCase
{
    private const NAME_PREFIX = 'Functional album controller ';

    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;
    private User $admin;
    private User $nonAdmin;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'ina@zaoui.com',
        ]);
        $this->nonAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 2]);

        $this->client->loginUser($this->admin);
    }

    protected function tearDown(): void
    {
        foreach ($this->entityManager->getRepository(Album::class)->findAll() as $album) {
            if (str_starts_with($album->getName(), self::NAME_PREFIX)) {
                $this->entityManager->remove($album);
            }
        }

        $this->entityManager->flush();
        parent::tearDown();
    }

    public function testIndexDisplaysAlbums(): void
    {
        $album = $this->createAlbum('listed');

        $this->client->request('GET', '/admin/album');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main h1', 'Albums');
        self::assertSelectorTextContains('tbody', $album->getName());
    }

    public function testNonAdminCannotAccessAlbumController(): void
    {
        $this->client->loginUser($this->nonAdmin);

        $this->client->request('GET', '/admin/album');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2.home-title', 'Photographe');
    }

    public function testAddAlbumDisplaysForm(): void
    {
        $this->client->request('GET', '/admin/album/add');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#album_name');
        self::assertSelectorTextContains('main h1', 'Albums');
    }

    public function testAddAlbumPersistsAlbumAndRedirectsToIndex(): void
    {
        $name = self::NAME_PREFIX.'added';
        $this->client->request('GET', '/admin/album/add');
        $form = $this->client->getCrawler()->selectButton('Ajouter')->form([
            'album[name]' => $name,
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/admin/album');
        self::assertNotNull($this->entityManager->getRepository(Album::class)->findOneBy([
            'name' => $name,
        ]));
    }

    public function testUpdateAlbumPersistsChangesAndRedirectsToIndex(): void
    {
        $album = $this->createAlbum('before update');
        $name = self::NAME_PREFIX.'updated';

        $this->client->request('GET', '/admin/album/update/'.$album->getId());
        $form = $this->client->getCrawler()->selectButton('Modifier')->form([
            'album[name]' => $name,
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/admin/album');
        $this->entityManager->clear();
        self::assertSame($name, $this->entityManager->getRepository(Album::class)->find($album->getId())->getName());
    }

    public function testDeleteAlbumRemovesAlbumAndRedirectsToIndex(): void
    {
        $album = $this->createAlbum('to delete');
        $albumId = $album->getId();

        $this->client->request('GET', '/admin/album/delete/'.$albumId);

        self::assertResponseRedirects('/admin/album');
        self::assertNull($this->entityManager->getRepository(Album::class)->find($albumId));
    }

    private function createAlbum(string $suffix): Album
    {
        $album = new Album();
        $album->setName(self::NAME_PREFIX.$suffix);
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        return $album;
    }
}
