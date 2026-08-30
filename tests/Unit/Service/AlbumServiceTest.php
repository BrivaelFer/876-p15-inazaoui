<?php

namespace App\Tests\Unit;

use App\Entity\Album;
use App\Entity\Media;
use App\Repository\MediaRepository;
use App\Service\AlbumService;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AlbumServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private MediaRepository&MockObject $mediaRepository;
    private MediaService&MockObject $mediaService;
    private AlbumService $albumService;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mediaRepository = $this->createMock(MediaRepository::class);
        $this->mediaService = $this->createMock(MediaService::class);
        $this->albumService = new AlbumService(
            $this->entityManager,
            $this->mediaRepository,
            $this->mediaService
        );
    }

    #region Tests

    /**
     * @param Album $album
     * @param Media[] $medias
     * @return void
     */
    #[DataProvider('deleteAlbumProvider')]
    public function testDeleteAlbumDeletesItsMediaAndAlbum(Album $album, array $medias): void
    {
        $this->mediaRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['album' => $album])
            ->willReturn($medias);

        $deletedMedias = [];
        $this->mediaService
            ->expects(self::exactly(count($medias)))
            ->method('deleteMedia')
            ->willReturnCallback(function (Media $media, bool $flush) use (&$deletedMedias): void {
                $deletedMedias[] = [$media, $flush];
            });
        $this->entityManager->expects(self::once())->method('remove')->with($album);
        $this->entityManager->expects(self::once())->method('flush');

        $this->albumService->deleteAlbum($album);

        self::assertSame(
            array_map(static fn (Media $media): array => [$media, false], $medias),
            $deletedMedias
        );
    }

    #endregion

    #region DataProviders

    /**
     * @return array<string, array{Album, Media[]}>
     */
    public static function deleteAlbumProvider(): array
    {
        return [
            'empty album' => [new Album(), []],
            'album with medias' => [new Album(), [new Media(), new Media()]],
        ];
    }

    #endregion
}