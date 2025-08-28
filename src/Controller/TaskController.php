<?php

namespace App\Controller;

use App\Entity\Race;
use App\Entity\ScavengerHunt;
use App\Entity\Task;
use App\Form\GuessType;
use App\Form\TaskType;
use App\Repository\ParticipantRepository;
use App\Repository\TaskRepository;
use App\Service\GenericHelper;
use App\Service\TaskHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TaskController extends AbstractController
{
    public function __construct(
        protected ParticipantRepository $participantRepository,
        protected GenericHelper $genericHelper,
        protected TaskHelper $taskHelper,
    ) {
    }

    #[Route(name: 'app_task_index', methods: ['GET'])]
    #[IsGranted('access_admin')]
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAll(),
        ]);
    }

    #[Route('/task/new/{id}', name: 'app_task_new', methods: ['GET', 'POST'])]
    #[IsGranted('view')]
    public function new(Request $request, ScavengerHunt $scavengerHunt, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskHelper->createTask($task, $scavengerHunt);

            return $this->redirectToRoute('app_scavenger_hunt_edit', ['id' => $scavengerHunt->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/task/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted('view', subject: 'task')]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager, int $id): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskHelper->editTask($task);

            return $this->redirectToRoute('app_scavenger_hunt_edit', ['id' => $task->getScavengerHunt()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/task/{id}/{uuid}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task, string $uuid): Response
    {
        $this->genericHelper->validateEntityUuid($task, $uuid);

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/guess/{id}/{uuid}/{race}', name: 'app_task_guess', methods: ['GET', 'POST'])]
    public function guess(Request $request, Task $task, Race $race, string $uuid, EntityManagerInterface $entityManager): Response
    {
        $this->genericHelper->validateEntityUuid($task, $uuid);

        $participant = $this->genericHelper->getCurrentParticipant();

        $guessForm = $this->createForm(GuessType::class);
        $guessForm->handleRequest($request);

        if ($guessForm->isSubmitted() && $guessForm->isValid()) {
            if ($this->taskHelper->validateGuess($guessForm, $task, $participant)) {
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

    #[Route('/task/{id}', name: 'app_task_delete', methods: ['POST'])]
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
