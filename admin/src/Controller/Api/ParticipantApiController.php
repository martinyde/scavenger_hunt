<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Highscore;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Repository\TaskRepository;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Participants')]
final class ParticipantApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RaceHelper $raceHelper,
    ) {
    }

    #[Route('/api/v1/participants/by-uuid/{uuid}', name: 'api_participant_by_uuid', methods: ['GET'])]
    #[OA\Get(summary: 'Get participant by UUID')]
    #[OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Participant details',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'progressEntryCount', type: 'integer'),
            new OA\Property(property: 'progressSolutionCount', type: 'integer'),
            new OA\Property(property: 'finished', type: 'boolean'),
            new OA\Property(property: 'finishedTime', type: 'integer', nullable: true),
            new OA\Property(property: 'raceId', type: 'integer', nullable: true),
        ]),
    )]
    #[OA\Response(response: 404, description: 'Participant not found')]
    public function byUuid(string $uuid, ParticipantRepository $repository): JsonResponse
    {
        $participant = $repository->findOneBy(['uuid' => $uuid]);

        if (!$participant) {
            return $this->json(['error' => 'Participant not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $participant->getId(),
            'uuid' => $participant->getUuid()->toString(),
            'name' => $participant->getName(),
            'progressEntryCount' => $participant->getProgressEntryCount(),
            'progressSolutionCount' => $participant->getProgressSolutionCount(),
            'finished' => $participant->isFinished(),
            'finishedTime' => $participant->getFinishedTime(),
            'raceId' => $participant->getRace()?->getId(),
        ]);
    }

    #[Route('/api/v1/participants/{id}/guess-access-key', name: 'api_participant_guess_access_key', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Submit an access key guess')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Participant ID', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['raceId', 'guess'],
            properties: [
                new OA\Property(property: 'raceId', type: 'integer'),
                new OA\Property(property: 'guess', type: 'string', example: 'secret-key'),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Number of matched tasks',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'matched', type: 'integer'),
        ]),
    )]
    #[OA\Response(response: 400, description: 'Missing required fields or participant mismatch')]
    public function guessAccessKey(
        Participant $participant,
        Request $request,
        TaskRepository $taskRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['raceId'], $data['guess'])) {
            return $this->json(['error' => 'Missing required fields: raceId, guess'], Response::HTTP_BAD_REQUEST);
        }

        $race = $participant->getRace();
        if (!$race || $race->getId() !== (int) $data['raceId']) {
            return $this->json(['error' => 'Participant does not belong to this race'], Response::HTTP_BAD_REQUEST);
        }

        $scavengerHuntId = $race->getScavengerHunt()->getId();
        $tasks = $taskRepository->findBy(['scavengerHunt' => $scavengerHuntId]);
        $guess = $data['guess'];
        $matchedCount = 0;

        foreach ($tasks as $task) {
            // Skip if participant already has access to this task
            if ($participant->getProgressTaskEntry()->contains($task)) {
                continue;
            }

            // Case insensitive comparison of guess against task passKey
            if (strtolower((string) $guess) === strtolower((string) $task->getPassKey())) {
                $participant->setProgressEntryCount(($participant->getProgressEntryCount() ?? 0) + 1);
                $participant->addProgressTaskEntry($task);
                ++$matchedCount;
            }
        }

        if ($matchedCount > 0) {
            $this->entityManager->persist($participant);
            $this->entityManager->flush();

            $this->raceHelper->publishParticipantUpdate($participant, $race);
        }

        return $this->json(['matched' => $matchedCount]);
    }

    #[Route('/api/v1/participants/{id}/finish', name: 'api_participant_finish', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Mark participant as finished and create highscore')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Participant ID', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Participant finished',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'progressEntryCount', type: 'integer'),
            new OA\Property(property: 'progressSolutionCount', type: 'integer'),
            new OA\Property(property: 'finished', type: 'boolean'),
            new OA\Property(property: 'finishedTime', type: 'integer', nullable: true),
            new OA\Property(property: 'raceId', type: 'integer'),
        ]),
    )]
    #[OA\Response(response: 400, description: 'Participant has no associated race')]
    public function finish(Participant $participant): JsonResponse
    {
        $race = $participant->getRace();
        if (!$race) {
            return $this->json(['error' => 'Participant has no associated race'], Response::HTTP_BAD_REQUEST);
        }

        // Calculate finished time (seconds since race start)
        $finishedTime = null;
        if ($race->getTimeStart()) {
            $now = new \DateTimeImmutable();
            $finishedTime = $now->getTimestamp() - $race->getTimeStart()->getTimestamp();
        }

        $participant->setFinished(true);
        $participant->setFinishedTime($finishedTime);

        // Create highscore entry
        $highscore = new Highscore();
        $highscore->setParticipant($participant);
        $highscore->setParticipantName($participant->getName());
        $highscore->setRace($race);
        $highscore->setScavengerHunt($race->getScavengerHunt());
        $highscore->setProgressTaskEntry($participant->getProgressEntryCount() ?? 0);
        $highscore->setProgressTaskSolution($participant->getProgressSolutionCount() ?? 0);
        $highscore->setTime($finishedTime ?? 0);
        $highscore->setCreated(new \DateTime());

        $this->entityManager->persist($participant);
        $this->entityManager->persist($highscore);
        $this->entityManager->flush();

        return $this->json([
            'id' => $participant->getId(),
            'uuid' => $participant->getUuid()->toString(),
            'name' => $participant->getName(),
            'progressEntryCount' => $participant->getProgressEntryCount(),
            'progressSolutionCount' => $participant->getProgressSolutionCount(),
            'finished' => $participant->isFinished(),
            'finishedTime' => $participant->getFinishedTime(),
            'raceId' => $race->getId(),
        ]);
    }
}
