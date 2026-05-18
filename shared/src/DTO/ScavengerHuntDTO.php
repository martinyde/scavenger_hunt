<?php

declare(strict_types=1);

namespace Scavenger\Shared\DTO;

readonly class ScavengerHuntDTO
{
    /**
     * @param TaskDTO[] $tasks
     * @param RaceDTO[] $races
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $tasks = [],
        public array $races = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $tasks = array_map(
            fn(array $task) => TaskDTO::fromArray($task),
            $data['tasks'] ?? [],
        );

        $races = array_map(
            fn(array $race) => RaceDTO::fromArray($race),
            $data['races'] ?? [],
        );

        return new self(
            id: $data['id'],
            name: $data['name'],
            tasks: $tasks,
            races: $races,
        );
    }
}
