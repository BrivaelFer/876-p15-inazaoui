<?php

namespace App\Tests\Unit;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class MediaServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private MediaRepository&MockObject $mediaRepository;
    private MediaService $mediaService;
    private string $uploadFolder;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mediaRepository = $this->createMock(MediaRepository::class);
        $this->uploadFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'media-service-test' . DIRECTORY_SEPARATOR;
        mkdir($this->uploadFolder, 0777, true);
        $this->mediaService = new MediaService(
            $this->entityManager,
            $this->mediaRepository,
            $this->uploadFolder
        );
    }

    public function tearDown(): void
    {
        foreach (glob($this->uploadFolder . '*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->uploadFolder);

        parent::tearDown();
    }

    #region Tests

    #[DataProvider('findIndexMediasProvider')]
    public function testFindIndexMedias(int $page, array $criteria, array $medias): void
    {
        $this->mediaRepository
            ->expects($this->once())
            ->method('findBy')
            ->with($criteria, ['id' => 'ASC'], 25, 25 * ($page - 1))
            ->willReturn($medias);

        self::assertSame($medias, $this->mediaService->findIndexMedias($page, $criteria));
    }

    #[DataProvider('mediasCountProvider')]
    public function testMediasCount(array $criteria, int $count): void
    {
        $this->mediaRepository
            ->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn($count);

        self::assertSame($count, $this->mediaService->mediasCount($criteria));
    }

    #[DataProvider('addMediaProvider')]
    public function testAddMediaStoresFileAssociatesUserAndPersistsMedia(?User $user): void
    {
        $media = new Media();
        $file = $this->createMock(File::class);

        $file->expects($this->once())->method('guessExtension')->willReturn('jpg');
        $file
            ->expects($this->once())
            ->method('move')
            ->with($this->uploadFolder, $this->callback(
                fn (string $path): bool => str_ends_with($path, '.jpg') && strlen(basename($path)) === 36
            ));
        $media->setFile($file);

        $this->entityManager->expects($this->once())->method('persist')->with($media);
        $this->entityManager->expects($this->once())->method('flush');

        $this->mediaService->addMedia($media, $user);

        self::assertSame($user, $media->getUser());
        self::assertMatchesRegularExpression(
            '#^' . preg_quote($this->uploadFolder, '#') . '[a-f0-9]{32}\.jpg$#',
            $media->getPath()
        );
    }

    #[DataProvider('deleteMediaProvider')]
    public function testDeleteMediaRemovesFileAndOptionallyFlushes(Media $media, string $path, bool $flush): void
    {
        file_put_contents($path, 'media');
        self::assertFileExists($path);

        $this->entityManager->expects($this->once())->method('remove')->with($media);
        $this->entityManager
            ->expects($flush ? $this->once() : $this->never())
            ->method('flush');

        $this->mediaService->deleteMedia($media, $flush);

        self::assertFileDoesNotExist($path);
    }

    #endregion

    #region DataProviders

    public static function findIndexMediasProvider(): array
    {
        return [
            'first page without criteria' => [1, [], []],
            'third page with criteria' => [3, ['album' => 2], [new Media()]],
        ];
    }

    public static function mediasCountProvider(): array
    {
        return [
            'no criteria' => [[], 0],
            'filtered medias' => [['user' => 1], 4],
        ];
    }

    public static function addMediaProvider(): array
    {
        return [
            'without user' => [null],
            'with user' => [new User()],
        ];
    }

    public static function deleteMediaProvider(): array
    {
        return [
            'with flush' => self::mediaDeletionData('media-with-flush.jpg', true),
            'without flush' => self::mediaDeletionData('media-without-flush.jpg', false),
        ];
    }

    private static function mediaDeletionData(string $filename, bool $flush): array
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'media-service-test' . DIRECTORY_SEPARATOR . $filename;
        $media = new Media();
        $media->setPath($path);

        return [$media, $path, $flush];
    }

    #endregion
}