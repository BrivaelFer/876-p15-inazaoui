<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private string $adminEmail
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setName('Ina Zaoui');
        $admin->setEmail($this->adminEmail);
        $admin->setDescription('Administratrice du portfolio.');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        for ($guestNumber = 0; $guestNumber < 55; $guestNumber++) {
            $guest = new User();
            $guest->setName(sprintf('Invité %d', $guestNumber));
            $guest->setEmail(sprintf('invite+%d@example.com', $guestNumber));
            $guest->setDescription('Photographe invité de la galerie.');
            $guest->setRoles(['ROLE_ACTIVE_USER']);
            $guest->setPassword($this->passwordHasher->hashPassword($guest, 'password'. $guestNumber));
            $manager->persist($guest);
        }

        $manager->flush();
    }
}
