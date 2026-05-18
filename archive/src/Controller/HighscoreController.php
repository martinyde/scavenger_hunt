<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HighscoreController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
    ) {
    }

    #[Route('/highscores/race/{raceId}', name: 'app_highscore_race', methods: ['GET'])]
    public function raceHighscores(int $raceId, Request $request): Response
    {
        $highscores = $this->adminApiClient->getRaceHighscores($raceId);

        if ($this->wantsJson($request)) {
            return $this->json(array_map(fn ($h) => [
                'id' => $h->id,
                'participantName' => $h->participantName,
                'progressTaskEntry' => $h->progressTaskEntry,
                'progressTaskSolution' => $h->progressTaskSolution,
                'time' => $h->time,
            ], $highscores));
        }

        return $this->render('highscore/index.html.twig', [
            'highscores' => $highscores,
            'title' => 'Race Highscores',
        ]);
    }

    #[Route('/highscores/hunt/{huntId}', name: 'app_highscore_hunt', methods: ['GET'])]
    public function huntHighscores(int $huntId, Request $request): Response
    {
        $highscores = $this->adminApiClient->getScavengerHuntHighscores($huntId);

        if ($this->wantsJson($request)) {
            return $this->json(array_map(fn ($h) => [
                'id' => $h->id,
                'participantName' => $h->participantName,
                'progressTaskEntry' => $h->progressTaskEntry,
                'progressTaskSolution' => $h->progressTaskSolution,
                'time' => $h->time,
            ], $highscores));
        }

        return $this->render('highscore/index.html.twig', [
            'highscores' => $highscores,
            'title' => 'Hunt Highscores',
        ]);
    }

    #[Route('/api/highscores/race/{raceId}', name: 'api_highscore_race', methods: ['GET'])]
    public function apiRaceHighscores(int $raceId): JsonResponse
    {
        $highscores = $this->adminApiClient->getRaceHighscores($raceId);

        return $this->json(array_map(fn ($h) => [
            'id' => $h->id,
            'participantName' => $h->participantName,
            'progressTaskEntry' => $h->progressTaskEntry,
            'progressTaskSolution' => $h->progressTaskSolution,
            'time' => $h->time,
            'created' => $h->created,
        ], $highscores));
    }

    #[Route('/api/highscores/hunt/{huntId}', name: 'api_highscore_hunt', methods: ['GET'])]
    public function apiHuntHighscores(int $huntId): JsonResponse
    {
        $highscores = $this->adminApiClient->getScavengerHuntHighscores($huntId);

        return $this->json(array_map(fn ($h) => [
            'id' => $h->id,
            'participantName' => $h->participantName,
            'progressTaskEntry' => $h->progressTaskEntry,
            'progressTaskSolution' => $h->progressTaskSolution,
            'time' => $h->time,
            'created' => $h->created,
        ], $highscores));
    }

    private function wantsJson(Request $request): bool
    {
        return str_contains($request->headers->get('Accept', ''), 'application/json');
    }
}
