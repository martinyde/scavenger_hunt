<?php

namespace App\DataFixtures;

use App\Entity\Highscore;
use App\Entity\Race;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class HighScoreFixtures extends Fixture implements DependentFixtureInterface
{

  /**
   * @throws \Random\RandomException
   */
  public function load(ObjectManager $manager): void
  {

    $fixtureHighScores = [
      ['race' => 'race:1', 'created' => new \DateTime('2025-03-15')],
      ['race' => 'race:2', 'created' => new \DateTime('2025-08-15')],
      ['race' => 'race:3', 'created' => new \DateTime('2025-06-15')]
    ];

    foreach ($fixtureHighScores as $fixtureHighScore) {
      $scavengerHunt = $this->getReference($fixtureHighScore['race'], Race::class)->getScavengerHunt();
      $participants = $this->getReference($fixtureHighScore['race'], Race::class)->getParticipants();
      foreach ($participants as $participant) {
        $highScore = new Highscore();
        $highScore->setParticipant($participant);
        $highScore->setRace($this->getReference($fixtureHighScore['race'], Race::class));
        $highScore->setScavengerHunt($scavengerHunt);
        $highScore->setParticipantName($participant->getName());
        $highScore->setProgressTaskEntry($participant->getProgressEntryCount() ?? 10);
        $highScore->setProgressTaskSolution($participant->getProgressSolutionCount() ?? 8);
        $highScore->setTime($participant->getFinishedTime() ?? random_int(400, 3600));
        $highScore->setCreated($fixtureHighScore['created']);
        $manager->persist($highScore);
      }
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
