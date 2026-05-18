<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HighscoreController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
    ) {
    }

    #[Route('/highscore/race/{raceId}', name: 'app_highscore_race', methods: ['GET'])]
    public function raceHighscores(int $raceId): Response
    {
        $highscores = $this->adminApiClient->getRaceHighscores($raceId);

        return $this->render('highscore/index.html.twig', [
            'highscores' => $highscores,
        ]);
    }
}
