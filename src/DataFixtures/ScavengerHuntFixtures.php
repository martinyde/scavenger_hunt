<?php

namespace App\DataFixtures;

use App\Entity\ScavangerHunt;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScavengerHuntFixtures extends Fixture implements DependentFixtureInterface
{    public function load(ObjectManager $manager): void
    {
      $fixtureScavengerHunts = [
        ['name' => 'TreasureHunter'],
        ['name' => 'ClueSeeker'],
        ['name' => 'PuzzleMaster'],
        ['name' => 'QuestFinder'],
        ['name' => 'RiddleSolver'],
        ['name' => 'HuntChampion'],
        ['name' => 'MapReader'],
        ['name' => 'ArtifactCollector'],
        ['name' => 'ClueTracker'],
        ['name' => 'TreasureTracker'],
        ['name' => 'ScavengerPro'],
        ['name' => 'HiddenObjectFinder'],
        ['name' => 'PrizeSeeker'],
        ['name' => 'MysteryExplorer'],
        ['name' => 'TokenCollector'],
        ['name' => 'CheckpointChaser'],
        ['name' => 'HuntNavigator'],
        ['name' => 'SecretFinder'],
        ['name' => 'CipherDecoder'],
        ['name' => 'AdventureHunter'],
      ];

      foreach ($fixtureScavengerHunts as $key => $fixtureScavengerHunt) {
        $scavengerHunt = new ScavangerHunt();
        $scavengerHunt->setName($fixtureScavengerHunt['name']);
        $scavengerHunt->setUser(
          $this->getReference(
            $key > 15 ? 'user:noah@example.com' : 'user:ava@example.com',
            User::class
          )
        );

        $manager->persist($scavengerHunt);

        $this->setReference('scavenger-hunt:' . $fixtureScavengerHunt['name'], $scavengerHunt);
      }

      $manager->flush();
    }

    public function getDependencies(): array
    {
      return [
        UserFixtures::class,
      ];
    }
}
