<?php

namespace App\Tests\Unit;

use App\Entity\Album;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AlbumEntityTest extends TestCase
{
    #[DataProvider('albumProvider')]
    public function testNameCanBeSetAndReadBack(string $name, ?int $expectedId): void
    {
        $album = new Album();

        $album->setName($name);

        self::assertSame($name, $album->getName());
        self::assertSame($expectedId, $album->getId());
    }

    public static function albumProvider(): array
    {
        return [
            'named album' => ['Vacances 2026', null],
            'another named album' => ['Portraits', null],
        ];
    }
}
