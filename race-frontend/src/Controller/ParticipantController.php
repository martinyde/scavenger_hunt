<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipantController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
    ) {
    }

    #[Route('/participant/add/{raceId}/{raceUuid}', name: 'app_participant_add', methods: ['GET', 'POST'])]
    public function add(int $raceId, string $raceUuid, Request $request): Response
    {
        $race = $this->adminApiClient->getRace($raceId);
        if ($race->uuid !== $raceUuid) {
            throw $this->createAccessDeniedException('UUID mismatch');
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name', '');
            $participant = $this->adminApiClient->addParticipant($raceId, $name);

            $cookie = new Cookie(
                'participant',
                $participant->uuid,
                new \DateTimeImmutable('+1 week'),
            );

            $response = $this->redirectToRoute('app_race_show', [
                'id' => $race->id,
                'uuid' => $race->uuid,
                'participantUuid' => $participant->uuid,
            ]);
            $response->headers->setCookie($cookie->withPartitioned());

            return $response;
        }

        return $this->render('participant/add.html.twig', [
            'race' => $race,
        ]);
    }

    #[Route('/participant/join/{raceId}/{raceUuid}/{participantId}', name: 'app_participant_join', methods: ['GET'])]
    public function join(int $raceId, string $raceUuid, int $participantId): Response
    {
        $race = $this->adminApiClient->getRace($raceId);
        if ($race->uuid !== $raceUuid) {
            throw $this->createAccessDeniedException('UUID mismatch');
        }

        // Look up participant to get their UUID
        $participants = $this->adminApiClient->getRaceParticipants($raceId);
        $participantUuid = null;
        foreach ($participants as $p) {
            if ($p->id === $participantId) {
                $participantUuid = $p->uuid;
                break;
            }
        }

        if (!$participantUuid) {
            throw $this->createNotFoundException('Participant not found');
        }

        $cookie = new Cookie(
            'participant',
            $participantUuid,
            new \DateTimeImmutable('+1 week'),
        );

        $response = $this->redirectToRoute('app_race_show', [
            'id' => $race->id,
            'uuid' => $race->uuid,
            'participantUuid' => $participantUuid,
        ]);
        $response->headers->setCookie($cookie->withPartitioned());

        return $response;
    }

    #[Route('/race/participant-finish/{participantId}/{participantUuid}/{raceId}', name: 'app_race_participant_finish', methods: ['GET'])]
    public function finish(int $participantId, string $participantUuid, int $raceId): Response
    {
        $this->adminApiClient->finishParticipant($participantId, $participantUuid);

        return $this->redirectToRoute('app_highscore_race', ['raceId' => $raceId]);
    }
}
