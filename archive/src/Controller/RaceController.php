<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RaceController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%race_frontend_url%')]
        private readonly string $raceFrontendUrl,
    ) {
    }

    #[Route('/races/create/{huntId}', name: 'app_race_create', methods: ['GET', 'POST'])]
    public function create(int $huntId, Request $request): Response
    {
        $hunt = $this->adminApiClient->getScavengerHunt($huntId);

        if ($request->isMethod('POST')) {
            $duration = (int) $request->request->get('duration', 300);
            $type = $request->request->get('type', 'standard');

            $race = $this->adminApiClient->createRace($huntId, $duration, $type);

            return $this->render('race/created.html.twig', [
                'race' => $race,
                'hunt' => $hunt,
                'raceFrontendUrl' => $this->raceFrontendUrl,
            ]);
        }

        return $this->render('race/create.html.twig', [
            'hunt' => $hunt,
        ]);
    }

    #[Route('/api/races', name: 'api_race_create', methods: ['POST'])]
    public function apiCreate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $scavengerHuntId = $data['scavenger_hunt_id'] ?? null;
        $duration = $data['duration'] ?? null;
        $type = $data['type'] ?? 'standard';

        if (!$scavengerHuntId) {
            return $this->json(['error' => 'scavenger_hunt_id is required'], Response::HTTP_BAD_REQUEST);
        }

        $race = $this->adminApiClient->createRace($scavengerHuntId, $duration, $type);

        return $this->json([
            'id' => $race->id,
            'uuid' => $race->uuid,
            'active' => $race->active,
            'type' => $race->type,
            'raceDuration' => $race->raceDuration,
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/races/{uuid}', name: 'api_race_show', methods: ['GET'])]
    public function apiShow(string $uuid): JsonResponse
    {
        $race = $this->adminApiClient->getRaceByUuid($uuid);

        return $this->json([
            'id' => $race->id,
            'uuid' => $race->uuid,
            'active' => $race->active,
            'finished' => $race->finished,
            'raceDuration' => $race->raceDuration,
            'type' => $race->type,
            'participantCount' => $race->participantCount,
            'scavengerHunt' => $race->scavengerHunt ? [
                'id' => $race->scavengerHunt->id,
                'name' => $race->scavengerHunt->name,
            ] : null,
        ]);
    }
}
