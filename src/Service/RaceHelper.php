<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\ScavangerHunt;
use App\Form\TryType;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\UuidV7;
use Twig\Environment;

class RaceHelper {

  public function __construct(
    protected EntityManagerInterface $entityManager,
    protected ParticipantRepository $participantRepository,
    protected TaskRepository $taskRepository,
    protected RequestStack $request,
    protected HubInterface $hub,
    protected FormFactoryInterface $formFactory,
    protected Environment $twig,
  ) {

  }

  /**
   * @param \App\Entity\Race $race
   * @param \App\Entity\ScavangerHunt $scavengerHunt
   *
   * @return \App\Entity\Race
   */
  public function createRace(Race $race, ScavangerHunt $scavengerHunt): Race {
    $race->setScavengerHunt($scavengerHunt);
    $race->setUuid(new UuidV7());
    $race->setActive(false);
    $this->entityManager->persist($race);
    $this->entityManager->flush();

    return $race;
  }

  /**
   * @param \App\Entity\Race $race
   * @param \Symfony\Component\Form\FormInterface $form
   * @param string $participantUuid
   *
   * @return void
   */
  public function guessAccessKey(Race $race, FormInterface $form, string $participantUuid): void {
    $participant = $this->getParticipant($participantUuid);
    $raceScavengerHuntId = $race->getScavengerHunt()->getId();
    $tasks = $this->taskRepository->findBy(['scavangerHunt' => $raceScavengerHuntId]);

    foreach ($tasks as $task) {
      if ($this->validateAccessKey($form, $task, $participant)) {
        $participant->setProgressEntryCount($participant->getProgressEntryCount() + 1);
        $participant->addProgressTaskEntry($task);
        $this->entityManager->persist($participant);
        $this->entityManager->flush();
      }
    }
  }

  /**
   * @param Race $race
   *
   * @return void
   */
  public function startRace(Race $race) {
    $race->setTimeStart(new DatePoint());
    $race->setActive(true);
    $this->entityManager->flush();

    $tryForm = $this->formFactory->create(TryType::class);
    try {
      $update = new Update(
        'race_state_changed',
        $this->twig->render('broadcast/Try.form.stream.html.twig', [
          'race' => $race,
          'tryform' => $tryForm->createView(),
        ])
      );

      $this->hub->publish($update);
    } catch (\Exception $exception) {
      // @todo what do we want here?
    }
  }

  /**
   * @param \App\Entity\Race $race
   *
   * @return string
   */
  public function getSecondsLeft(Race $race): string {
    $now = new DatePoint();

    try {
      return $race->getTimeStart() ? $race->getTimeStart()->modify('+' . $race->getRaceDuration() . ' seconds')->getTimestamp() - $now->getTimestamp(): $race->getRaceDuration();
    } catch (\Throwable $exception) {
      // @todo what do we want here?
    }
  }

  /**
   *
   * @param $uuid
   *
   * @return \App\Entity\Participant|null
   */
  public function getParticipant($uuid): ?Participant {
    return $this->participantRepository->findOneBy(['uuid' => $uuid]);
  }

  /**
   * Get list of all finished races.
   *
   * @return array
   */
  public function getFinishedRaces(): array {
    /** @var RaceRepository $repo */
    $repo = $this->entityManager->getRepository(Race::class);

    return $repo->findFinishedRaces();
  }

  /**
   * Whether a race is finished.
   *
   * @param \App\Entity\Race $race
   *
   * @return bool
   */
  public function isFinished(Race $race): bool {
    return !$race->isActive() && !empty($race->getTimer());
  }

  /**
   * Remove finished races.
   *
   * @return void
   */
  public function removeFinishedRaces(): void {
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
   * Validate the access key typed in form against a task.
   *
   * @param $form
   * @param $task
   * @param $participant
   *
   * @return bool
   */
  private function validateAccessKey($form, $task, $participant): bool {
    if ($participant->getProgressTaskEntry()->contains($task)) {
      return false;
    }
    $formData = $form->get('try')->getData();
    $passkey = $task->getPassKey();

    if (strtolower($formData) !== strtolower($passkey)) {
      return false;
    }

    return true;
  }
}