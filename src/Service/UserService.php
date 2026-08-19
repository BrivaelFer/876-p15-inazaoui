<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $hasher,
        private MediaService $mediaService
    ) {}

    public function findUsersToIndex(int $page): array
    {
        return $this->userRepository->findActiveUsers($page, 25);
    }

    public function usersCount(): int
    {
        return $this->userRepository->count() - 1;
    }

    public function addUser(User $user): void
    {
        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
        $user->setRoles(['ROLE_ACTIVE_USER']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function deleteUserData(User $user): void
    {
        $this->removeUserMedia($user);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
    
    public function deactivateUser(User $user): void
    {
        if(in_array('ROLE_ACTIVE_USER', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->removeRole('ROLE_ACTIVE_USER');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function activateUser(User $user): void
    {
        if(!$user->hasRole('ROLE_ACTIVE_USER')) {
            $user->addRole('ROLE_ACTIVE_USER');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    private function removeUserMedia(User $user): void
    {
        foreach($user->getMedias() as $media) {
            $this->mediaService->deleteMedia($media, false);
        }
    }
}