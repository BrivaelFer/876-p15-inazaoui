<?php

namespace App\DataFixtures;

use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AlbumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 5; $i++) { 
            $album = new Album();
            $album->setName('Album ' . ($i+1));
            $manager->persist($album);
        }

        $manager->flush();
    }
}
