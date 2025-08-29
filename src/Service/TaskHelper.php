<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\ScavengerHunt;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class TaskHelper
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function createTask(Task $task, ScavengerHunt $scavengerHunt): void
    {
        $task->setScavengerHunt($scavengerHunt);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    public function editTask(Task $task): void
    {
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
   *  Validate the submitted guess.
   *
   * @param \Symfony\Component\Form\FormInterface $form
   * @param \App\Entity\Task $task
   * @param \App\Entity\Participant $participant
   *
   * @return bool
   */
    public function validateGuess(FormInterface $form, Task $task, Participant $participant): bool
    {
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
