<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RaceController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
    ) {
    }

    #[Route(
        '/race/{id}/{uuid}/{participantUuid}',
        name: 'app_race_show',
        requirements: [
            'id' => '\d+',
            // UUIDs only — without this constraint the route greedy-matches
            // partial endpoints like /race/{id}/partial/race-content and
            // raises a 403 instead of letting Symfony route to
            // app_race_partial_content.
            'uuid' => '[0-9a-fA-F-]{36}',
            'participantUuid' => '[0-9a-fA-F-]{36}',
        ],
        methods: ['GET'],
    )]
    public function show(int $id, string $uuid, Request $request, ?string $participantUuid = null): Response
    {
        $race = $this->adminApiClient->getRace($id);
        if ($race->uuid !== $uuid) {
            throw $this->createAccessDeniedException('UUID mismatch');
        }

        $participantUuid = $participantUuid ?? $request->cookies->get('participant');
        $participant = null;
        if ($participantUuid) {
            try {
                $participant = $this->adminApiClient->getParticipantByUuid($participantUuid);
            } catch (\Exception) {
                // participant not found
            }
        }

        $participants = $this->adminApiClient->getRaceParticipants($id);
        $timer = $this->adminApiClient->getRaceTimer($id);
        $scavengerHunt = $this->adminApiClient->getScavengerHunt($race->scavengerHunt->id);

        return $this->render('race/show.html.twig', [
            'race' => $race,
            'participant' => $participant,
            'participants' => $participants,
            'timer' => $timer,
            'scavengerHunt' => $scavengerHunt,
        ]);
    }

    #[Route('/race/form/try/{raceId}', name: 'app_form_try', methods: ['POST'])]
    public function try(int $raceId, Request $request): Response
    {
        $participantUuid = $request->cookies->get('participant');
        if (!$participantUuid) {
            throw $this->createAccessDeniedException('Not a participant');
        }

        $guess = $request->request->get('try', '');
        $race = $this->adminApiClient->getRace($raceId);

        $participant = $this->adminApiClient->getParticipantByUuid($participantUuid);
        $this->adminApiClient->guessAccessKey($raceId, $participantUuid, $guess);

        return $this->redirectToRoute('app_race_show', [
            'id' => $race->id,
            'uuid' => $race->uuid,
            'participantUuid' => $participantUuid,
        ]);
    }

    #[Route('/race/{id}/progress', name: 'app_race_progress', methods: ['GET'])]
    public function progress(int $id): Response
    {
        $race = $this->adminApiClient->getRace($id);
        $participants = $this->adminApiClient->getRaceParticipants($id);
        $timer = $this->adminApiClient->getRaceTimer($id);
        $scavengerHunt = $this->adminApiClient->getScavengerHunt($race->scavengerHunt->id);

        return $this->render('race/progress.html.twig', [
            'race' => $race,
            'participants' => $participants,
            'timer' => $timer,
            'scavengerHunt' => $scavengerHunt,
        ]);
    }

    #[Route('/race/{raceId}/partial/race-content', name: 'app_race_partial_content', methods: ['GET'])]
    public function partialRaceContent(int $raceId, Request $request): Response
    {
        $race = $this->adminApiClient->getRace($raceId);
        // Prefer the explicit query param (sent by the participant view's
        // Mercure controller) and fall back to the cookie. The cookie path
        // covers callers that don't know the participant uuid (e.g. the
        // admin progress page), but it isn't reliable when the cookie is
        // missing — see `mercure_controller.js`.
        $participantUuid = $request->query->get('participantUuid')
            ?: $request->cookies->get('participant');
        $participant = null;
        if ($participantUuid) {
            try {
                $participant = $this->adminApiClient->getParticipantByUuid($participantUuid);
            } catch (\Exception) {}
        }
        $participants = $this->adminApiClient->getRaceParticipants($raceId);
        $timer = $this->adminApiClient->getRaceTimer($raceId);
        $scavengerHunt = $this->adminApiClient->getScavengerHunt($race->scavengerHunt->id);

        return $this->render('race/_race-content.html.twig', [
            'race' => $race,
            'participant' => $participant,
            'participants' => $participants,
            'timer' => $timer,
            'scavengerHunt' => $scavengerHunt,
        ]);
    }

    #[Route('/race/{raceId}/partial/participants', name: 'app_race_partial_participants', methods: ['GET'])]
    public function partialParticipants(int $raceId): Response
    {
        $race = $this->adminApiClient->getRace($raceId);
        $participants = $this->adminApiClient->getRaceParticipants($raceId);
        $scavengerHunt = $this->adminApiClient->getScavengerHunt($race->scavengerHunt->id);

        return $this->render('race/_participants-list.html.twig', [
            'race' => $race,
            'participants' => $participants,
            'scavengerHunt' => $scavengerHunt,
        ]);
    }
}
