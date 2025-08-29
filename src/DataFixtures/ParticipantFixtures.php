<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $fixtureParticipants = [
            ['name' => 'John', 'race' => 'race:1'],
            ['name' => 'Emma', 'race' => 'race:1'],
            ['name' => 'Michael', 'race' => 'race:1'],
            ['name' => 'Sophia', 'race' => 'race:1'],
            ['name' => 'William', 'race' => 'race:1'],
            ['name' => 'Olivia', 'race' => 'race:1'],
            ['name' => 'James', 'race' => 'race:1'],
            ['name' => 'Isabella', 'race' => 'race:1'],
            ['name' => 'Benjamin', 'race' => 'race:1'],
            ['name' => 'Charlotte', 'race' => 'race:1'],
            ['name' => 'Lucas', 'race' => 'race:1'],
            ['name' => 'Amelia', 'race' => 'race:1'],
            ['name' => 'Henry', 'race' => 'race:1'],
            ['name' => 'Mia', 'race' => 'race:1'],
            ['name' => 'Alexander', 'race' => 'race:1'],
            ['name' => 'Harper', 'race' => 'race:1'],
            ['name' => 'Daniel', 'race' => 'race:1'],
            ['name' => 'Evelyn', 'race' => 'race:1'],
            ['name' => 'Matthew', 'race' => 'race:1'],
            ['name' => 'Abigail', 'race' => 'race:1'],
            ['name' => 'Noah', 'race' => 'race:2'],
            ['name' => 'Ava', 'race' => 'race:2'],
            ['name' => 'Ethan', 'race' => 'race:2'],
            ['name' => 'Lily', 'race' => 'race:2'],
            ['name' => 'Jacob', 'race' => 'race:2'],
            ['name' => 'Grace', 'race' => 'race:2'],
            ['name' => 'Logan', 'race' => 'race:2'],
            ['name' => 'Chloe', 'race' => 'race:2'],
            ['name' => 'Jackson', 'race' => 'race:2'],
            ['name' => 'Zoe', 'race' => 'race:2'],
            ['name' => 'Aiden', 'race' => 'race:2'],
            ['name' => 'Ella', 'race' => 'race:2'],
            ['name' => 'Samuel', 'race' => 'race:2'],
            ['name' => 'Scarlett', 'race' => 'race:2'],
            ['name' => 'David', 'race' => 'race:2'],
            ['name' => 'Layla', 'race' => 'race:2'],
            ['name' => 'Joseph', 'race' => 'race:2'],
            ['name' => 'Madison', 'race' => 'race:2'],
            ['name' => 'Owen', 'race' => 'race:2'],
            ['name' => 'Victoria', 'race' => 'race:3'],
        ];

        foreach ($fixtureParticipants as $fixtureParticipant) {
            $participant = new Participant();
            $participant->setName($fixtureParticipant['name']);
            $participant->setRace($this->getReference($fixtureParticipant['race'], Race::class));

            $participant->addProgressTaskEntry($this->getReference('task:oak', Task::class));
            $participant->addProgressTaskEntry($this->getReference('task:chronos', Task::class));
            $manager->persist($participant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RaceFixtures::class,
        ];
    }
}
