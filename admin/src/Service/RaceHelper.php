<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\ScavengerHunt;
use App\Entity\Task;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\UuidV7;

class RaceHelper
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ParticipantRepository $participantRepository,
        protected TaskRepository $taskRepository,
        protected RequestStack $request,
        protected HubInterface $hub,
    ) {
    }

    public function createRace(Race $race, ScavengerHunt $scavengerHunt): Race
    {
        $race->setScavengerHunt($scavengerHunt);
        $race->setUuid(new UuidV7());
        $race->setActive(false);
        $this->entityManager->persist($race);
        $this->entityManager->flush();

        return $race;
    }

    public function guessAccessKey(Race $race, FormInterface $form, string $participantUuid): void
    {
        $participant = $this->getParticipant($participantUuid);
        $raceScavengerHuntId = $race->getScavengerHunt()->getId();
        $tasks = $this->taskRepository->findBy(['scavengerHunt' => $raceScavengerHuntId]);

        foreach ($tasks as $task) {
            if ($this->validateAccessKey($form, $task, $participant)) {
                $participant->setProgressEntryCount($participant->getProgressEntryCount() + 1);
                $participant->addProgressTaskEntry($task);
                $this->entityManager->persist($participant);
                $this->entityManager->flush();

                $this->publishParticipantUpdate($participant, $race);
            }
        }
    }

    /**
     * Guess access key from raw string input (used by API).
     */
    public function guessAccessKeyRaw(Race $race, string $participantUuid, string $guess): int
    {
        $participant = $this->getParticipant($participantUuid);
        $raceScavengerHuntId = $race->getScavengerHunt()->getId();
        $tasks = $this->taskRepository->findBy(['scavengerHunt' => $raceScavengerHuntId]);
        $matched = 0;

        foreach ($tasks as $task) {
            if ($participant->getProgressTaskEntry()->contains($task)) {
                continue;
            }
            if (strtolower($guess) === strtolower((string) $task->getPassKey())) {
                $participant->setProgressEntryCount($participant->getProgressEntryCount() + 1);
                $participant->addProgressTaskEntry($task);
                $this->entityManager->persist($participant);
                $this->entityManager->flush();
                ++$matched;

                $this->publishParticipantUpdate($participant, $race);
            }
        }

        return $matched;
    }

    public function startRace(Race $race): void
    {
        $race->setTimeStart(new DatePoint());
        $race->setActive(true);
        $this->entityManager->flush();

        $this->publishRaceStateChanged($race);
    }

    public function finishRace(Race $race): void
    {
        $race->setActive(false);
        $this->entityManager->flush();

        $this->publishRaceStateChanged($race);
    }

    public function getSecondsLeft(Race $race): int
    {
        $now = new DatePoint();

        try {
            return $race->getTimeStart() ? $race->getTimeStart()->modify('+'.$race->getRaceDuration().' seconds')->getTimestamp() - $now->getTimestamp() : $race->getRaceDuration();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getParticipant(string $uuid): ?Participant
    {
        return $this->participantRepository->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @return array<mixed>
     */
    public function getFinishedRaces(): array
    {
        /** @var RaceRepository $repo */
        $repo = $this->entityManager->getRepository(Race::class);

        return $repo->findFinishedRaces();
    }

    public function isFinished(Race $race): bool
    {
        return !$race->isActive() && !empty($race->getTimer());
    }

    public function removeFinishedRaces(): void
    {
        $finishedRaces = $this->getFinishedRaces();
        foreach ($finishedRaces as $race) {
            foreach ($race->getParticipants() as $participant) {
                $this->entityManager->remove($participant);
            }
            $this->entityManager->remove($race);
        }
        $this->entityManager->flush();
    }

    /**
     * Publish a JSON race state change to Mercure.
     */
    public function publishRaceStateChanged(Race $race): void
    {
        try {
            $update = new Update(
                'race/'.$race->getId(),
                json_encode([
                    'type' => 'race_state_changed',
                    'raceId' => $race->getId(),
                    'active' => $race->isActive(),
                    'finished' => $this->isFinished($race),
                ])
            );
            $this->hub->publish($update);
        } catch (\Exception) {
            // Mercure may not be available
        }
    }

    /**
     * Publish a JSON participant update to Mercure.
     */
    public function publishParticipantUpdate(Participant $participant, Race $race): void
    {
        try {
            $update = new Update(
                'race/'.$race->getId().'/participants',
                json_encode([
                    'type' => 'participant_updated',
                    'raceId' => $race->getId(),
                    'participantId' => $participant->getId(),
                    'name' => $participant->getName(),
                    'progressEntryCount' => $participant->getProgressEntryCount(),
                    'progressSolutionCount' => $participant->getProgressSolutionCount(),
                ])
            );
            $this->hub->publish($update);
        } catch (\Exception) {
            // Mercure may not be available
        }
    }

    /**
     * Publish a JSON participant added event to Mercure.
     */
    public function publishParticipantAdded(Participant $participant, Race $race): void
    {
        try {
            $update = new Update(
                'race/'.$race->getId().'/participants',
                json_encode([
                    'type' => 'participant_added',
                    'raceId' => $race->getId(),
                    'participantId' => $participant->getId(),
                    'name' => $participant->getName(),
                ])
            );
            $this->hub->publish($update);
        } catch (\Exception) {
            // Mercure may not be available
        }
    }

    private function validateAccessKey(FormInterface $form, Task $task, Participant $participant): bool
    {
        if ($participant->getProgressTaskEntry()->contains($task)) {
            return false;
        }
        $formData = $form->get('try')->getData();
        $passkey = $task->getPassKey();

        if (strtolower((string) $formData) !== strtolower((string) $passkey)) {
            return false;
        }

        return true;
    }
}
