<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
      private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
      $fixtureUsers = [
          ['email' => 'noah@example.com'],
          ['email' => 'ava@example.com'],
          ['email' => 'ethan@example.com'],
          ['email' => 'lily@example.com'],
          ['email' => 'jacob@example.com'],
          ['email' => 'grace@example.com'],
          ['email' => 'logan@example.com'],
          ['email' => 'chloe@example.com'],
          ['email' => 'jackson@example.com'],
          ['email' => 'zoe@example.com'],
          ['email' => 'aiden@example.com'],
          ['email' => 'ella@example.com'],
          ['email' => 'samuel@example.com'],
          ['email' => 'scarlett@example.com'],
          ['email' => 'david@example.com'],
          ['email' => 'layla@example.com'],
          ['email' => 'joseph@example.com'],
          ['email' => 'madison@example.com'],
          ['email' => 'owen@example.com'],
          ['email' => 'victoria@example.com'],
      ];

      foreach ($fixtureUsers as $fixtureUser) {
        // create the user and hash its password
        $user = new User();
        $user->setEmail($fixtureUser['email']);
        $user->setRoles([]);

        // See https://symfony.com/doc/5.4/security.html#registering-the-user-hashing-passwords
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'lorem');
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        $this->setReference('user:' . $fixtureUser['email'], $user);
      }

      $manager->flush();
    }
}
