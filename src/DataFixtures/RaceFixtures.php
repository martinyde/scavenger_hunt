<?php

namespace App\DataFixtures;

use App\Entity\Race;
use App\Entity\ScavangerHunt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RaceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $race = new Race();
        $race->setRaceDuration(7200);
        $race->setScavengerHunt($this->getReference('scavenger-hunt:TreasureHunter', ScavangerHunt::class));
        $manager->persist($race);
        $this->setReference('race:1', $race);

        $race = new Race();
        $race->setRaceDuration(3600);
        $race->setScavengerHunt($this->getReference('scavenger-hunt:TreasureHunter', ScavangerHunt::class));
        $manager->persist($race);
        $this->setReference('race:2', $race);

        $race = new Race();
        $race->setRaceDuration(7200);
        $race->setScavengerHunt($this->getReference('scavenger-hunt:ClueSeeker', ScavangerHunt::class));
        $manager->persist($race);
        $this->setReference('race:3', $race);

        $manager->flush();
    }

    public function getDependencies(): array
    {
      return [
        ScavengerHuntFixtures::class,
      ];
    }

}
