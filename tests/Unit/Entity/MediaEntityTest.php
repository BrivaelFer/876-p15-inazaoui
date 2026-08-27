<?php

namespace App\Tests\Unit;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class MediaEntityTest extends TestCase
{
    #[DataProvider('mediaProvider')]
    public function testMediaAccessorsAndRelationsAreStoredCorrectly(
        User $user,
        Album $album,
        string $path,
        string $title,
        File $file,
        ?int $expectedId
    ): void
    {
        $media = new Media();

        $media->setUser($user);
        $media->setAlbum($album);
        $media->setPath($path);
        $media->setTitle($title);
        $media->setFile($file);

        self::assertSame($expectedId, $media->getId());
        self::assertSame($user, $media->getUser());
        self::assertSame($album, $media->getAlbum());
        self::assertSame($path, $media->getPath());
        self::assertSame($title, $media->getTitle());
        self::assertSame($file, $media->getFile());
    }

    /**
     * @return array<string, array{User, Album, string, string, File, int|null}>
     */
    public static function mediaProvider(): array
    {
        return [
            'uploaded_image' => [
                new User(),
                new Album(),
                '/uploads/test.jpg',
                'Mon media',
                new File(__FILE__),
                null,
            ],
            'another_image' => [
                new User(),
                new Album(),
                '/uploads/portrait.jpg',
                'Portrait',
                new File(__FILE__),
                null,
            ],
        ];
    }
}
