<?php

namespace App\DataFixtures;

use App\Entity\Race;
use App\Entity\ScavengerHunt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RaceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $race = new Race();
        $race->setRaceDuration(7200);
        $race->setActive(false);
        $race->setScavengerHunt($this->getReference('scavenger-hunt:TreasureHunter', ScavengerHunt::class));
        $race->setType('single');
        $manager->persist($race);
        $this->setReference('race:1', $race);

        $race = new Race();
        $race->setRaceDuration(3600);
        $race->setActive(false);
        $race->setScavengerHunt($this->getReference('scavenger-hunt:TreasureHunter', ScavengerHunt::class));
        $race->setType('single');
        $manager->persist($race);
        $this->setReference('race:2', $race);

        $race = new Race();
        $race->setRaceDuration(7200);
        $race->setActive(false);
        $race->setType('repeating');
        $race->setScavengerHunt($this->getReference('scavenger-hunt:ClueSeeker', ScavengerHunt::class));
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
