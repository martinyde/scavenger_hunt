<?php

namespace App\Controller;

use App\Entity\Race;
use App\Entity\Task;
use App\Form\GuessType;
use App\Form\TaskType;
use App\Repository\ParticipantRepository;
use App\Repository\TaskRepository;
use App\Service\GenericHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/task')]
final class TaskController extends AbstractController
{
    public function __construct(
      protected ParticipantRepository $participantRepository,
      protected GenericHelper         $genericHelper,
    )
    {
    }

    #[Route(name: 'app_task_index', methods: ['GET'])]
    #[IsGranted('access_admin')]
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    #[IsGranted('view')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/{uuid}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task, string $uuid): Response
    {
        if ($task->getUuid()->toString() !== $uuid) {
          throw $this->createAccessDeniedException();
        }
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted('view')]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/guess/{id}/{uuid}/{race}', name: 'app_task_guess', methods: ['GET', 'POST'])]
    public function guess(Request $request, Task $task, Race $race, string $uuid, EntityManagerInterface $entityManager): Response
    {
      $this->genericHelper->validateEntityUuid($task, $uuid);

      $participant = $this->genericHelper->getCurrentParticipant();

      $guessForm = $this->createForm(GuessType::class);
      $guessForm->handleRequest($request);

      if ($guessForm->isSubmitted() && $guessForm->isValid()) {
        if (in_array($guessForm->get('guess')->getData(), $task->getSolutions()) && !$participant->getProgressTaskSolution()->contains($task)) {
          $participant->setProgressSolutionCount($participant->getProgressSolutionCount() + 1);
          $participant->addProgressTaskSolution($task);
          $entityManager->persist($participant);
          $entityManager->flush();

          return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid()->toString(), 'participantUuid' => $participant->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_task_guess', ['id' => $task->getId(), 'uuid' => $task->getUuid()->toString(), 'race' => $race->getId()], Response::HTTP_SEE_OTHER);
      }

      return $this->render('task/guess.html.twig', [
        'task' => $task,
        'guessForm' => $guessForm,
        'race' => $race,
        'participant' => $participant,
      ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    #[IsGranted('view')]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
