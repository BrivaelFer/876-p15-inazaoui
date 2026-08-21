<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MediaRepository $mediaRepository,
        private string $uploadFolder
    ) {}

    public function findIndexMedias(int $page, array $criteria): array
    {
        return $this->mediaRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
    }

    public function mediasCount(array $criteria): int
    {
        return $this->mediaRepository->count($criteria);
    }

    public function addMedia(Media $media, ?User $user): void
    {
        if(null !== $user){
            $media->setUser($user);
        }

        $media->setPath($this->uploadFolder . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
        $media->getFile()->move($this->uploadFolder, $media->getPath());

        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }
    
    public function deleteMedia(Media $media, bool $flush = true): void
    {
        $this->entityManager->remove($media);
        if ($flush) $this->entityManager->flush();

        $this->deleteMediaFile($media);
    }

    private function deleteMediaFile(Media $media): void
    {
        unlink($media->getPath());
    }
}