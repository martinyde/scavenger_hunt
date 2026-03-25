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

class GenericHelper
{
    public function __construct(
        protected RequestStack $request,
        protected EntityManagerInterface $entityManager,
        protected ParticipantRepository $participantRepository,
        protected HighscoreRepository $highscoreRepository,
    ) {
    }

    /**
     * Confirm that a given Uuid matches a specific race.
     *
     * @param Race|Task $entity
     */
    public function validateEntityUuid(Race|Task|Participant $entity, string $uuid): void
    {
        if ($entity->getUuid()->toString() !== $uuid) {
            throw new AccessDeniedException('UUID Mismatch');
        }
    }

    /**
     * Set a cookie for the participant that lasts a week.
     */
    public function setParticpantCookie(Participant $participant): Cookie
    {
        $this->removeParticipantCookie();
        $cookie = new Cookie(
            'participant',
            $participant->getUuid(),
            new \DateTimeImmutable('+1 week'),
        );

        return $cookie->withPartitioned();
    }

  /**
   * Remove participant cookie.
   *
   * @return void
   */
    public function removeParticipantCookie(): void
    {
      $this->request->getCurrentRequest()->cookies->remove('participant');
    }

  /**
   * Get the current participant from uuid or from cookie.
   *
   * @param string|null $participantUuid
   *
   * @return \App\Entity\Participant|null
   */
    public function getCurrentParticipant(?string $participantUuid = null, ?Race $race = null): ?Participant
    {
        $raceParticipants = $race ? $race->getParticipants() : [];
        if (empty($participantUuid)) {
          $request = $this->request->getCurrentRequest();
          if ($request) {
            $participantUuid = $request->cookies->get('participant');
          }
        }

        $participant = $this->participantRepository->findOneBy(['uuid' => $participantUuid]);

        if ($race && !in_array($participant, $race->getParticipants()->toArray())) {
          return null;
        }

        return $participant ?? null;
    }

    /**
     * Add all participants to highscore table.
     */
    public function createHighscores(Race $race): void
    {
        foreach ($race->getParticipants() as $participant) {
            if ($this->highscoreRepository->findOneBy(['participant' => $participant])) {
                break;
            }
            $this->createHighscore($participant);
        }

        $this->entityManager->flush();
    }

  /**
   * Create a high score from a participant.
   */
    public function createHighscore(Participant $participant): ?Highscore
    {
        if (empty($participant->getStartTime())) {
          return null;
        }
        $highScore = new Highscore();
        $highScore->setParticipant($participant->getId());
        $highScore->setParticipantName($participant->getName());
        $highScore->setRace($participant->getRace()->getId());
        $highScore->setScavengerHunt($participant->getRace()->getScavengerHunt()->getId());
        $highScore->setProgressTaskEntry($participant->getProgressEntryCount() ?? 0);
        $highScore->setProgressTaskSolution($participant->getProgressSolutionCount() ?? 0);
        $highScore->setTime($participant->getStartTime()->diff(new \DateTime())->s);
        $highScore->setCreated(new \DateTime());
        $this->entityManager->persist($highScore);

        return $highScore;
    }
}
