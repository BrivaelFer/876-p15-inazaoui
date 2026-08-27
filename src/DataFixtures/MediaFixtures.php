<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;

class MediaFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private string $uploadFolder,
        private string $projectDir
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        $this->clearUploadFolders();

        $this->adminMedias($manager);
        $this->otherUserMedias($manager);

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [UserFixtures::class, AlbumFixtures::class];
    }

    private function adminMedias(ObjectManager $manager): void
    {
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);
        $albums = $manager->getRepository(Album::class)->findAll();

        $countAlbum = count($albums);
        for($i = 0; $i < 45; $i++) {
            $media = new Media();
            $media->setTitle('Media ' . ($i+1));
            $media->setUser($admin);
            $media->setPath($this->uploadFolder. md5(uniqid()) . '.jpg');
            copy(
                $this->projectDir. '/src/DataFixtures/Assets/img/fix-' .(($i % 4) + 1) . '.jpg', 
                $this->projectDir. '/public/' . $media->getPath()
            );
            $media->setAlbum($albums[$i % $countAlbum]);
            $manager->persist($media);
        }
    }

    private function otherUserMedias(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        foreach($users as $user) {
             if ($user->getEmail() === 'ina@zaoui.com') {
                continue;
            }
            for($i = 0; $i < 30; $i++) {
                $media = new Media();
                $media->setTitle('Media ' . $user->getId(). '-' . ($i+1));
                $media->setUser($user);
                $media->setPath($this->uploadFolder. md5(uniqid()) . '.jpg');
                copy(
                    $this->projectDir. '/src/DataFixtures/Assets/img/fix-' .(($i % 4) + 1) . '.jpg', 
                    $this->projectDir. '/public/' . $media->getPath()
                );
                $manager->persist($media);
            }
        }
    }

    
    private function clearUploadFolders(): void
    {
        $directories = [
            $this->projectDir . '/public/' . $this->uploadFolder,
            $this->projectDir . '/' . $this->uploadFolder,
        ];

        foreach ($directories as $directory) {
            foreach (glob($directory . '*') !== false ? glob($directory . '*') : [] as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    unlink($file);
                }
            }
        }
    }
}
