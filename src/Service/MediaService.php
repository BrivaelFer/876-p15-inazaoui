<?php

namespace App\Service;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}
    
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