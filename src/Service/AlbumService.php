<?php

namespace App\Service;

use App\Entity\Album;
use App\Entity\Media;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;

class AlbumService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MediaRepository $mediaRepository,
        private MediaService $mediaService
    ) {}
    
    public function deleteAlbum(Album $album): void
    {
        $medias = $this->mediaRepository->findBy(['album' => $album]);
        foreach($medias as $media) {
            $this->mediaService->deleteMedia($media, false);
        }
        $this->entityManager->remove($album);
        $this->entityManager->flush();
    }

}