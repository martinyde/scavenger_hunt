<?php
declare(strict_types=1);
namespace App\Controller\Api;

use App\Entity\Highscore;
use App\Entity\Race;
use App\Entity\ScavengerHunt;
use App\Repository\HighscoreRepository;
use App\Repository\RaceRepository;
use App\Repository\ScavengerHuntRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Highscores')]
#[Route('/api/v1/highscores', name: 'api_highscore_')]
final class HighscoreApiController extends AbstractController
{
    public function __construct(
        private readonly HighscoreRepository $highscoreRepository,
    ) {
    }

    #[Route('/race/{raceId}', name: 'race', methods: ['GET'], requirements: ['raceId' => '\d+'])]
    #[OA\Get(summary: 'Get highscores for a race')]
    #[OA\Parameter(name: 'raceId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
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
    #[OA\Response(response: 404, description: 'Race not found')]
    public function raceHighscores(int $raceId, RaceRepository $raceRepository): JsonResponse
    {
        $race = $raceRepository->find($raceId);
        if (!$race) {
            return $this->json(['error' => 'Race not found'], Response::HTTP_NOT_FOUND);
        }

        $highscores = $this->highscoreRepository->getRaceHighScores($race);

        return $this->json(array_map(fn(Highscore $h) => [
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

    #[Route('/scavenger-hunt/{scavengerHuntId}', name: 'scavenger_hunt', methods: ['GET'], requirements: ['scavengerHuntId' => '\d+'])]
    #[OA\Get(summary: 'Get highscores for a scavenger hunt')]
    #[OA\Parameter(name: 'scavengerHuntId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
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
    #[OA\Response(response: 404, description: 'Scavenger hunt not found')]
    public function scavengerHuntHighscores(int $scavengerHuntId, ScavengerHuntRepository $scavengerHuntRepository): JsonResponse
    {
        $scavengerHunt = $scavengerHuntRepository->find($scavengerHuntId);
        if (!$scavengerHunt) {
            return $this->json(['error' => 'Scavenger hunt not found'], Response::HTTP_NOT_FOUND);
        }

        $highscores = $this->highscoreRepository->getScavengerHuntHighScores($scavengerHunt);

        return $this->json(array_map(fn(Highscore $h) => [
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
}
