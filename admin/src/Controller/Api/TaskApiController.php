<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Task;
use App\Repository\ParticipantRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Tasks')]
final class TaskApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/v1/tasks/{id}', name: 'api_task_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Get task by ID')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Task details',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'passKey', type: 'string'),
            new OA\Property(property: 'textBefore', type: 'string', nullable: true),
            new OA\Property(property: 'textAfter', type: 'string', nullable: true),
        ]),
    )]
    public function show(Task $task): JsonResponse
    {
        return $this->json([
            'id' => $task->getId(),
            'uuid' => $task->getUuid()->toString(),
            'title' => $task->getTitle(),
            'passKey' => $task->getPassKey(),
            'textBefore' => $task->getTextBefore(),
            'textAfter' => $task->getTextAfter(),
        ]);
    }

    #[Route('/api/v1/tasks/by-uuid/{uuid}', name: 'api_task_by_uuid', methods: ['GET'])]
    #[OA\Get(summary: 'Get task by UUID')]
    #[OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Task details')]
    #[OA\Response(response: 404, description: 'Task not found')]
    public function byUuid(string $uuid, TaskRepository $repository): JsonResponse
    {
        $task = $repository->findOneBy(['uuid' => $uuid]);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $task->getId(),
            'uuid' => $task->getUuid()->toString(),
            'title' => $task->getTitle(),
            'passKey' => $task->getPassKey(),
            'textBefore' => $task->getTextBefore(),
            'textAfter' => $task->getTextAfter(),
        ]);
    }

    #[Route('/api/v1/tasks/{id}/guess', name: 'api_task_guess', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Submit a solution guess for a task')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['participantUuid', 'guess'],
            properties: [
                new OA\Property(property: 'participantUuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'guess', type: 'string', example: 'answer'),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Guess result',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'correct', type: 'boolean'),
        ]),
    )]
    #[OA\Response(response: 400, description: 'Missing required fields')]
    #[OA\Response(response: 404, description: 'Participant not found')]
    public function guess(Task $task, Request $request, ParticipantRepository $participantRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['participantUuid'], $data['guess'])) {
            return $this->json(['error' => 'Missing required fields: participantUuid, guess'], Response::HTTP_BAD_REQUEST);
        }

        $participant = $participantRepository->findOneBy(['uuid' => $data['participantUuid']]);
        if (!$participant) {
            return $this->json(['error' => 'Participant not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if participant already solved this task
        if ($participant->getProgressTaskSolution()->contains($task)) {
            return $this->json(['correct' => false]);
        }

        // Check if the guess matches any solution
        $guess = $data['guess'];
        if (!in_array($guess, $task->getSolutions())) {
            return $this->json(['correct' => false]);
        }

        // Update participant progress
        $participant->addProgressTaskSolution($task);
        $participant->setProgressSolutionCount(($participant->getProgressSolutionCount() ?? 0) + 1);
        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        return $this->json(['correct' => true]);
    }
}
