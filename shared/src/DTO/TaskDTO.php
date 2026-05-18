<?php

declare(strict_types=1);

namespace Scavenger\Shared\DTO;

readonly class TaskDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $title,
        public ?string $passKey,
        public ?string $textBefore,
        public ?string $textAfter,
        public array $solutions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            uuid: $data['uuid'],
            title: $data['title'],
            passKey: $data['passKey'] ?? null,
            textBefore: $data['textBefore'] ?? null,
            textAfter: $data['textAfter'] ?? null,
            solutions: $data['solutions'] ?? [],
        );
    }
}
