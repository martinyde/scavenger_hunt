<?php

namespace App\Entity;

use App\Repository\HighscoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HighscoreRepository::class)]
class Highscore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'highscore', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\ManyToOne(inversedBy: 'highscores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Race $race = null;

    #[ORM\ManyToOne(inversedBy: 'highscores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScavengerHunt $scavenger_hunt = null;

    #[ORM\Column]
    private ?int $progress_task_entry = null;

    #[ORM\Column]
    private ?int $progress_task_solution = null;

    #[ORM\Column]
    private ?int $time = null;

    #[ORM\Column]
    private ?\DateTime $created = null;

    #[ORM\Column(length: 255)]
    private ?string $participant_name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(Participant $participant): static
    {
        $this->participant = $participant;

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

    public function getScavengerHunt(): ?ScavengerHunt
    {
        return $this->scavenger_hunt;
    }

    public function setScavengerHunt(?ScavengerHunt $scavenger_hunt): static
    {
        $this->scavenger_hunt = $scavenger_hunt;

        return $this;
    }

    public function getProgressTaskEntry(): ?int
    {
        return $this->progress_task_entry;
    }

    public function setProgressTaskEntry(int $progress_task_entry): static
    {
        $this->progress_task_entry = $progress_task_entry;

        return $this;
    }

    public function getProgressTaskSolution(): ?int
    {
        return $this->progress_task_solution;
    }

    public function setProgressTaskSolution(int $progress_task_solution): static
    {
        $this->progress_task_solution = $progress_task_solution;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(int $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getParticipantName(): ?string
    {
        return $this->participant_name;
    }

    public function setParticipantName(string $participant_name): static
    {
        $this->participant_name = $participant_name;

        return $this;
    }
}
