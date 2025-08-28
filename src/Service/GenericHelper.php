<?php

namespace App\Service;

use App\Entity\Highscore;
use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\Task;
use App\Repository\HighscoreRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

class GenericHelper {

  public function __construct(
    protected RequestStack $request,
    protected EntityManagerInterface $entityManager,
    protected ParticipantRepository $participantRepository,
    protected HighscoreRepository $highscoreRepository,
  ) {}

  /**
   * Confirm that a given Uuid matches a specific race.
   *
   * @param \App\Entity\Race|\App\Entity\Task $entity
   * @param string $uuid
   *
   * @return void
   */
  public function validateEntityUuid(Race|Task|Participant $entity, string $uuid): void {
    if ($entity->getUuid()->toString() !== $uuid) {
      throw new AccessDeniedException('UUID Mismatch');
    }
  }

  /**
   * Set a cookie for the participant that lasts a week.
   *
   * @param \App\Entity\Participant $participant
   *
   * @return \Symfony\Component\HttpFoundation\Cookie
   */
  public function setParticpantCookie(Participant $participant): Cookie {
    $this->request->getCurrentRequest()->cookies->remove('participant');
    $cookie = new Cookie(
      'participant',
      $participant->getUuid(),
      new \DateTimeImmutable('+1 week'),
    );
    return $cookie->withPartitioned();
  }

  /**
   * Get the current participant from uuid or from cookie.
   *
   * @param string|NULL $participantUuid
   *
   * @return \App\Entity\Participant|null,
   */
  public function getCurrentParticipant(?string $participantUuid = NULL): ?Participant {
    if (empty($uuid)) {
      $participantUuid = $this->request->getCurrentRequest()->cookies->get('participant');
    }
    $participant = $this->participantRepository->findOneBy(['uuid' => $participantUuid]);

    return $participant ?? null;
  }

  /**
   * Add all participants to highscore table.
   *
   * @param \App\Entity\Race $race
   *
   * @return void
   */
  public function createHighscores(Race $race): void {
    foreach ($race->getParticipants() as $participant) {
      if ($this->highscoreRepository->findOneBy(['participant' => $participant])) {
        break;
      }
      $this->createHighscore($participant);
    }
    $this->entityManager->flush();
  }

  public function createHighscore(Participant $participant): void {
    $highScore = new Highscore();
    $highScore->setParticipant($participant);
    $highScore->setParticipantName($participant->getName());
    $highScore->setRace($participant->getRace());
    $highScore->setScavengerHunt($participant->getRace()->getScavengerHunt());
    $highScore->setProgressTaskEntry($participant->getProgressEntryCount() ?? 0);
    $highScore->setProgressTaskSolution($participant->getProgressSolutionCount() ?? 0);
    $highScore->setTime(123);
    $highScore->setCreated(new \DateTime());
    $this->entityManager->persist($highScore);
  }
}