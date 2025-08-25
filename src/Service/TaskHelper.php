<?php

namespace App\Service;

use App\Entity\ScavengerHunt;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

class TaskHelper {

  public function __construct(
    protected EntityManagerInterface $entityManager,
  ) {
  }

  public function createTask(Task $task, ScavengerHunt $scavengerHunt): void {
    $task->setScavengerHunt($scavengerHunt);
    $this->entityManager->persist($task);
    $this->entityManager->flush();
  }

  public function editTask(Task $task): void {
    $solutions = $task->getSolutions();
    foreach ($solutions as $key => $solution) {
      if (empty($solution)) {
        unset($solutions[$key]);
        $task->setSolutions($solutions);
      }
    }
    $this->entityManager->persist($task);
    $this->entityManager->flush();
  }

  /**
   * Validate the submitted guess.
   *
   * @param $form
   * @param $task
   * @param $participant
   *
   * @return bool
   */
  public function validateGuess($form, $task, $participant): bool {
    if ($participant->getProgressTaskSolution()->contains($task)) {
      return false;
    }

    $formData = $form->get('guess')->getData();
    if (!in_array($formData, $task->getSolutions())) {
      return false;
    }

    return true;
  }
}