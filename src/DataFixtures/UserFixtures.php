<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ADMIN
        $admin = new User();
        $admin->setUsername('admin@test.fr');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsBanned(false);
        $manager->persist($admin);

        // USER
        $user = new User();
        $user->setUsername('user@test.fr');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setRoles(['ROLE_USER']);
        $user->setIsBanned(false);
        $manager->persist($user);

        $manager->flush();
    }
}
