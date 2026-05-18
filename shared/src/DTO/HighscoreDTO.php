<?php

declare(strict_types=1);

namespace Scavenger\Shared\DTO;

readonly class HighscoreDTO
{
    public function __construct(
        public int $id,
        public string $participantName,
        public int $progressTaskEntry,
        public int $progressTaskSolution,
        public int $time,
        public string $created,
        public ?int $raceId,
        public ?int $scavengerHuntId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            participantName: $data['participantName'],
            progressTaskEntry: $data['progressTaskEntry'],
            progressTaskSolution: $data['progressTaskSolution'],
            time: $data['time'],
            created: $data['created'],
            raceId: $data['raceId'] ?? null,
            scavengerHuntId: $data['scavengerHuntId'] ?? null,
        );
    }
}
