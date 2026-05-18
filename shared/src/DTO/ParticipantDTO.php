<?php

declare(strict_types=1);

namespace Scavenger\Shared\DTO;

readonly class ParticipantDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $name,
        public ?int $progressEntryCount,
        public ?int $progressSolutionCount,
        public ?bool $finished,
        public ?int $finishedTime,
        public ?int $raceId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            uuid: $data['uuid'],
            name: $data['name'],
            progressEntryCount: $data['progressEntryCount'] ?? null,
            progressSolutionCount: $data['progressSolutionCount'] ?? null,
            finished: $data['finished'] ?? null,
            finishedTime: $data['finishedTime'] ?? null,
            raceId: $data['raceId'] ?? null,
        );
    }
}
