<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
    ) {
    }

    #[Route('/task/{id}/{uuid}', name: 'app_task_show', methods: ['GET'])]
    public function show(int $id, string $uuid): Response
    {
        $task = $this->adminApiClient->getTask($id);
        if ($task->uuid !== $uuid) {
            throw $this->createAccessDeniedException('UUID mismatch');
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/guess/{taskId}/{uuid}/{raceId}', name: 'app_task_guess', methods: ['GET', 'POST'])]
    public function guess(int $taskId, string $uuid, int $raceId, Request $request): Response
    {
        $task = $this->adminApiClient->getTask($taskId);
        if ($task->uuid !== $uuid) {
            throw $this->createAccessDeniedException('UUID mismatch');
        }

        $participantUuid = $request->cookies->get('participant');
        if (!$participantUuid) {
            throw $this->createAccessDeniedException('Not a participant');
        }

        if ($request->isMethod('POST')) {
            $guess = $request->request->get('guess', '');
            $result = $this->adminApiClient->guessSolution($taskId, $participantUuid, $guess);

            $race = $this->adminApiClient->getRace($raceId);

            if ($result['correct']) {
                return $this->redirectToRoute('app_race_show', [
                    'id' => $race->id,
                    'uuid' => $race->uuid,
                    'participantUuid' => $participantUuid,
                ]);
            }

            return $this->redirectToRoute('app_task_guess', [
                'taskId' => $taskId,
                'uuid' => $uuid,
                'raceId' => $raceId,
            ]);
        }

        $participant = $this->adminApiClient->getParticipantByUuid($participantUuid);
        $race = $this->adminApiClient->getRace($raceId);

        return $this->render('task/guess.html.twig', [
            'task' => $task,
            'race' => $race,
            'participant' => $participant,
        ]);
    }
}
