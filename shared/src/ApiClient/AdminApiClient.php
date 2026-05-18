<?php

declare(strict_types=1);

namespace Scavenger\Shared\ApiClient;

use Scavenger\Shared\DTO\HighscoreDTO;
use Scavenger\Shared\DTO\ParticipantDTO;
use Scavenger\Shared\DTO\RaceDTO;
use Scavenger\Shared\DTO\ScavengerHuntDTO;
use Scavenger\Shared\DTO\TaskDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdminApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $adminApiUrl,
        private string $apiKey,
    ) {}

    private function request(string $method, string $path, array $options = []): array
    {
        $response = $this->httpClient->request($method, $this->adminApiUrl . '/api/v1' . $path, array_merge([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ],
        ], $options));

        return $response->toArray();
    }

    /**
     * @return ScavengerHuntDTO[]
     */
    public function getScavengerHunts(): array
    {
        $data = $this->request('GET', '/scavenger-hunts');

        return array_map(
            fn(array $item) => ScavengerHuntDTO::fromArray($item),
            $data,
        );
    }

    public function getScavengerHunt(int $id): ScavengerHuntDTO
    {
        $data = $this->request('GET', '/scavenger-hunts/' . $id);

        return ScavengerHuntDTO::fromArray($data);
    }

    public function getRace(int $id): RaceDTO
    {
        $data = $this->request('GET', '/races/' . $id);

        return RaceDTO::fromArray($data);
    }

    public function getRaceByUuid(string $uuid): RaceDTO
    {
        $data = $this->request('GET', '/races/by-uuid/' . $uuid);

        return RaceDTO::fromArray($data);
    }

    /**
     * @return ParticipantDTO[]
     */
    public function getRaceParticipants(int $raceId): array
    {
        $data = $this->request('GET', '/races/' . $raceId . '/participants');

        return array_map(
            fn(array $item) => ParticipantDTO::fromArray($item),
            $data,
        );
    }

    /**
     * @return HighscoreDTO[]
     */
    public function getRaceHighscores(int $raceId): array
    {
        $data = $this->request('GET', '/races/' . $raceId . '/highscores');

        return array_map(
            fn(array $item) => HighscoreDTO::fromArray($item),
            $data,
        );
    }

    /**
     * @return HighscoreDTO[]
     */
    public function getScavengerHuntHighscores(int $scavengerHuntId): array
    {
        $data = $this->request('GET', '/highscores/scavenger-hunt/' . $scavengerHuntId);

        return array_map(
            fn(array $item) => HighscoreDTO::fromArray($item),
            $data,
        );
    }

    /**
     * @return array{secondsLeft: int, duration: int, raceState: bool}
     */
    public function getRaceTimer(int $raceId): array
    {
        return $this->request('GET', '/races/' . $raceId . '/timer');
    }

    public function getTask(int $id): TaskDTO
    {
        $data = $this->request('GET', '/tasks/' . $id);

        return TaskDTO::fromArray($data);
    }

    public function getTaskByUuid(string $uuid): TaskDTO
    {
        $data = $this->request('GET', '/tasks/by-uuid/' . $uuid);

        return TaskDTO::fromArray($data);
    }

    public function getParticipantByUuid(string $uuid): ParticipantDTO
    {
        $data = $this->request('GET', '/participants/by-uuid/' . $uuid);

        return ParticipantDTO::fromArray($data);
    }

    public function createRace(int $scavengerHuntId, ?int $duration, string $type): RaceDTO
    {
        $data = $this->request('POST', '/races', [
            'json' => [
                'scavengerHuntId' => $scavengerHuntId,
                'duration' => $duration,
                'type' => $type,
            ],
        ]);

        return RaceDTO::fromArray($data);
    }

    public function startRace(int $raceId): void
    {
        $this->request('POST', '/races/' . $raceId . '/start');
    }

    public function finishRace(int $raceId): void
    {
        $this->request('POST', '/races/' . $raceId . '/finish');
    }

    public function addParticipant(int $raceId, string $name): ParticipantDTO
    {
        $data = $this->request('POST', '/races/' . $raceId . '/participants', [
            'json' => [
                'name' => $name,
            ],
        ]);

        return ParticipantDTO::fromArray($data);
    }

    /**
     * @return array{matched: bool, tasks_found: int}
     */
    public function guessAccessKey(int $raceId, string $participantUuid, string $guess): array
    {
        // The guess-access-key endpoint is on the participant API
        // We need the participant ID, but we have the UUID - pass it via the race endpoint
        // The ParticipantApiController expects participantId in the URL and raceId + guess in the body
        $participant = $this->getParticipantByUuid($participantUuid);

        return $this->request('POST', '/participants/' . $participant->id . '/guess-access-key', [
            'json' => [
                'raceId' => $raceId,
                'guess' => $guess,
            ],
        ]);
    }

    /**
     * @return array{correct: bool}
     */
    public function guessSolution(int $taskId, string $participantUuid, string $guess): array
    {
        return $this->request('POST', '/tasks/' . $taskId . '/guess', [
            'json' => [
                'participantUuid' => $participantUuid,
                'guess' => $guess,
            ],
        ]);
    }

    public function finishParticipant(int $participantId, string $uuid): void
    {
        $this->request('POST', '/participants/' . $participantId . '/finish', [
            'json' => [
                'uuid' => $uuid,
            ],
        ]);
    }
}
