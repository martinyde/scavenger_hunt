<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Participant;
use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\ScavengerHuntRepository;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Races')]
final class RaceApiController extends AbstractController
{
    public function __construct(
        private readonly RaceHelper $raceHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/v1/races/{id}', name: 'api_race_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Get race details by ID')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Race details',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'active', type: 'boolean'),
            new OA\Property(property: 'finished', type: 'boolean'),
            new OA\Property(property: 'raceDuration', type: 'integer', nullable: true),
            new OA\Property(property: 'type', type: 'string'),
            new OA\Property(property: 'timeStart', type: 'string', format: 'date-time', nullable: true),
            new OA\Property(property: 'participantCount', type: 'integer'),
            new OA\Property(property: 'scavengerHunt', properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'taskCount', type: 'integer'),
            ], type: 'object'),
        ]),
    )]
    public function show(Race $race): JsonResponse
    {
        $scavengerHunt = $race->getScavengerHunt();

        return $this->json([
            'id' => $race->getId(),
            'uuid' => $race->getUuid()->toString(),
            'active' => $race->isActive(),
            'finished' => $this->raceHelper->isFinished($race),
            'raceDuration' => $race->getRaceDuration(),
            'type' => $race->getType(),
            'timeStart' => $race->getTimeStart()?->format('c'),
            'participantCount' => $race->getParticipants()->count(),
            'scavengerHunt' => [
                'id' => $scavengerHunt->getId(),
                'name' => $scavengerHunt->getName(),
                'taskCount' => $scavengerHunt->getTasks()->count(),
            ],
        ]);
    }

    #[Route('/api/v1/races/by-uuid/{uuid}', name: 'api_race_by_uuid', methods: ['GET'])]
    #[OA\Get(summary: 'Get race details by UUID')]
    #[OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Race details')]
    #[OA\Response(response: 404, description: 'Race not found')]
    public function byUuid(string $uuid, RaceRepository $repository): JsonResponse
    {
        $race = $repository->findOneBy(['uuid' => $uuid]);

        if (!$race) {
            return $this->json(['error' => 'Race not found'], Response::HTTP_NOT_FOUND);
        }

        $scavengerHunt = $race->getScavengerHunt();

        return $this->json([
            'id' => $race->getId(),
            'uuid' => $race->getUuid()->toString(),
            'active' => $race->isActive(),
            'finished' => $this->raceHelper->isFinished($race),
            'raceDuration' => $race->getRaceDuration(),
            'type' => $race->getType(),
            'timeStart' => $race->getTimeStart()?->format('c'),
            'participantCount' => $race->getParticipants()->count(),
            'scavengerHunt' => [
                'id' => $scavengerHunt->getId(),
                'name' => $scavengerHunt->getName(),
                'taskCount' => $scavengerHunt->getTasks()->count(),
            ],
        ]);
    }

    #[Route('/api/v1/races/{id}/participants', name: 'api_race_participants', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'List participants in a race')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'List of participants',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'progressEntryCount', type: 'integer'),
                new OA\Property(property: 'progressSolutionCount', type: 'integer'),
                new OA\Property(property: 'finished', type: 'boolean'),
                new OA\Property(property: 'finishedTime', type: 'integer', nullable: true),
                new OA\Property(property: 'raceId', type: 'integer'),
            ]),
        ),
    )]
    public function participants(Race $race): JsonResponse
    {
        $participants = $race->getParticipants();

        return $this->json(array_map(fn (Participant $p) => [
            'id' => $p->getId(),
            'uuid' => $p->getUuid()->toString(),
            'name' => $p->getName(),
            'progressEntryCount' => $p->getProgressEntryCount(),
            'progressSolutionCount' => $p->getProgressSolutionCount(),
            'finished' => $p->isFinished(),
            'finishedTime' => $p->getFinishedTime(),
            'raceId' => $race->getId(),
        ], $participants->toArray()));
    }

    #[Route('/api/v1/races/{id}/highscores', name: 'api_race_highscores', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Get highscores for a race')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'List of highscores',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'participantName', type: 'string'),
                new OA\Property(property: 'progressTaskEntry', type: 'integer'),
                new OA\Property(property: 'progressTaskSolution', type: 'integer'),
                new OA\Property(property: 'time', type: 'integer'),
                new OA\Property(property: 'created', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'raceId', type: 'integer', nullable: true),
                new OA\Property(property: 'scavengerHuntId', type: 'integer', nullable: true),
            ]),
        ),
    )]
    public function highscores(Race $race, \App\Repository\HighscoreRepository $highscoreRepository): JsonResponse
    {
        $highscores = $highscoreRepository->getRaceHighScores($race);

        return $this->json(array_map(fn (\App\Entity\Highscore $h) => [
            'id' => $h->getId(),
            'participantName' => $h->getParticipantName(),
            'progressTaskEntry' => $h->getProgressTaskEntry(),
            'progressTaskSolution' => $h->getProgressTaskSolution(),
            'time' => $h->getTime(),
            'created' => $h->getCreated()?->format('c'),
            'raceId' => $h->getRace()?->getId(),
            'scavengerHuntId' => $h->getScavengerHunt()?->getId(),
        ], $highscores));
    }

    #[Route('/api/v1/races/{id}/timer', name: 'api_race_timer', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Get race timer information')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Timer info',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'secondsLeft', type: 'integer', nullable: true),
            new OA\Property(property: 'duration', type: 'integer', nullable: true),
            new OA\Property(property: 'raceState', type: 'boolean'),
        ]),
    )]
    public function timer(Race $race): JsonResponse
    {
        return $this->json([
            'secondsLeft' => $this->raceHelper->getSecondsLeft($race),
            'duration' => $race->getRaceDuration(),
            'raceState' => $race->isActive(),
        ]);
    }

    #[Route('/api/v1/races', name: 'api_race_create', methods: ['POST'])]
    #[OA\Post(summary: 'Create a new race')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['scavengerHuntId', 'duration', 'type'],
            properties: [
                new OA\Property(property: 'scavengerHuntId', type: 'integer', example: 1),
                new OA\Property(property: 'duration', type: 'integer', example: 300, description: 'Duration in seconds'),
                new OA\Property(property: 'type', type: 'string', example: 'standard'),
            ],
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Race created',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'active', type: 'boolean'),
            new OA\Property(property: 'raceDuration', type: 'integer'),
            new OA\Property(property: 'type', type: 'string'),
        ]),
    )]
    #[OA\Response(response: 400, description: 'Missing required fields')]
    #[OA\Response(response: 404, description: 'Scavenger hunt not found')]
    public function create(Request $request, ScavengerHuntRepository $scavengerHuntRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['scavengerHuntId'], $data['duration'], $data['type'])) {
            return $this->json(['error' => 'Missing required fields: scavengerHuntId, duration, type'], Response::HTTP_BAD_REQUEST);
        }

        $scavengerHunt = $scavengerHuntRepository->find($data['scavengerHuntId']);
        if (!$scavengerHunt) {
            return $this->json(['error' => 'Scavenger hunt not found'], Response::HTTP_NOT_FOUND);
        }

        $race = new Race();
        $race->setRaceDuration((int) $data['duration']);
        $race->setType($data['type']);

        $this->raceHelper->createRace($race, $scavengerHunt);

        return $this->json([
            'id' => $race->getId(),
            'uuid' => $race->getUuid()->toString(),
            'active' => $race->isActive(),
            'raceDuration' => $race->getRaceDuration(),
            'type' => $race->getType(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/v1/races/{id}/start', name: 'api_race_start', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Start a race')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Race started',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'active', type: 'boolean'),
            new OA\Property(property: 'timeStart', type: 'string', format: 'date-time', nullable: true),
        ]),
    )]
    public function start(Race $race): JsonResponse
    {
        $this->raceHelper->startRace($race);

        return $this->json([
            'id' => $race->getId(),
            'active' => $race->isActive(),
            'timeStart' => $race->getTimeStart()?->format('c'),
        ]);
    }

    #[Route('/api/v1/races/{id}/finish', name: 'api_race_finish', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Finish a race')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Race finished',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'active', type: 'boolean'),
            new OA\Property(property: 'finished', type: 'boolean'),
        ]),
    )]
    public function finish(Race $race): JsonResponse
    {
        $this->raceHelper->finishRace($race);

        return $this->json([
            'id' => $race->getId(),
            'active' => $race->isActive(),
            'finished' => $this->raceHelper->isFinished($race),
        ]);
    }

    #[Route('/api/v1/races/{id}/participants', name: 'api_race_add_participant', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(summary: 'Add a participant to a race')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Alice'),
            ],
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Participant added',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'raceId', type: 'integer'),
        ]),
    )]
    #[OA\Response(response: 400, description: 'Missing required field: name')]
    public function addParticipant(Race $race, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'])) {
            return $this->json(['error' => 'Missing required field: name'], Response::HTTP_BAD_REQUEST);
        }

        $participant = new Participant();
        $participant->setName($data['name']);
        $participant->setRace($race);
        $participant->setProgressEntryCount(0);
        $participant->setProgressSolutionCount(0);
        $participant->setFinished(false);

        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        $this->raceHelper->publishParticipantAdded($participant, $race);

        return $this->json([
            'id' => $participant->getId(),
            'uuid' => $participant->getUuid()->toString(),
            'name' => $participant->getName(),
            'raceId' => $race->getId(),
        ], Response::HTTP_CREATED);
    }
}
