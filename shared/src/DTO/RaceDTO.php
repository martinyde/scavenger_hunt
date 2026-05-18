<?php

declare(strict_types=1);

namespace Scavenger\Shared\DTO;

readonly class RaceDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public bool $active,
        public ?bool $finished,
        public ?int $raceDuration,
        public ?string $type,
        public ?string $timeStart,
        public int $participantCount,
        public ?ScavengerHuntDTO $scavengerHunt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $scavengerHunt = isset($data['scavengerHunt']) && is_array($data['scavengerHunt'])
            ? ScavengerHuntDTO::fromArray($data['scavengerHunt'])
            : null;

        return new self(
            id: $data['id'],
            uuid: $data['uuid'],
            active: $data['active'],
            finished: $data['finished'] ?? null,
            raceDuration: $data['raceDuration'] ?? null,
            type: $data['type'] ?? null,
            timeStart: $data['timeStart'] ?? null,
            participantCount: $data['participantCount'] ?? 0,
            scavengerHunt: $scavengerHunt,
        );
    }
}
