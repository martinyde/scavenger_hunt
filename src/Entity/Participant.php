<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[Broadcast]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private array $progress_task_entry = [];

    #[ORM\Column]
    private array $progress_task_solution = [];

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Race $race = null;

    #[ORM\PrePersist]
    public function onPrePersist()
    {
      // This runs before the entity is first persisted
      $this->progress_task_entry = [];
      $this->progress_task_solution = [];
    }

  public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProgressTaskEntry(): array
    {
        return $this->progress_task_entry;
    }

    public function setProgressTaskEntry(array $progress_task_entry): static
    {
        $this->progress_task_entry = $progress_task_entry;

        return $this;
    }

    public function getProgressTaskSolution(): array
    {
        return $this->progress_task_solution;
    }

    public function setProgressTaskSolution(array $progress_task_solution): static
    {
        $this->progress_task_solution = $progress_task_solution;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;

        return $this;
    }
}
