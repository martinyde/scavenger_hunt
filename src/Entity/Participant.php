<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
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

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Race $race = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\ManyToMany(targetEntity: Task::class)]
    #[ORM\JoinTable(name: 'participant_task_entry')]
    private Collection $progress_task_entry;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\ManyToMany(targetEntity: Task::class)]
    #[ORM\JoinTable(name: 'participant_task_solution')]
    private Collection $progress_task_solution;

    #[ORM\Column(nullable: true)]
    private ?int $progress_entry_count = null;

    #[ORM\Column(nullable: true)]
    private ?int $progress_solution_count = null;

    #[ORM\Column(type: 'uuid')]
    private ?UuidV7 $uuid;

    #[ORM\OneToOne(mappedBy: 'participant', cascade: ['persist', 'remove'])]
    private ?Highscore $highscore = null;

    #[ORM\Column(nullable: true)]
    private ?bool $finished = null;

    #[ORM\Column(nullable: true)]
    private ?int $finished_time = null;

    public function __construct()
    {
        $this->progress_task_entry = new ArrayCollection();
        $this->progress_task_solution = new ArrayCollection();
        $this->uuid = new UuidV7();
    }

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

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;

        return $this;
    }
    public function __toString()
    {
      return $this->getName();
    }

    /**
     * @return Collection<int, Task>
     */
    public function getProgressTaskEntry(): Collection
    {
        return $this->progress_task_entry;
    }

    public function addProgressTaskEntry(Task $progressTaskEntry): static
    {
        if (!$this->progress_task_entry->contains($progressTaskEntry)) {
            $this->progress_task_entry->add($progressTaskEntry);
        }

        return $this;
    }

    public function removeProgressTaskEntry(Task $progressTaskEntry): static
    {
        $this->progress_task_entry->removeElement($progressTaskEntry);

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getProgressTaskSolution(): Collection
    {
        return $this->progress_task_solution;
    }

    public function addProgressTaskSolution(Task $progressTaskSolution): static
    {
        if (!$this->progress_task_solution->contains($progressTaskSolution)) {
            $this->progress_task_solution->add($progressTaskSolution);
        }

        return $this;
    }

    public function removeProgressTaskSolution(Task $progressTaskSolution): static
    {
        $this->progress_task_solution->removeElement($progressTaskSolution);

        return $this;
    }

    public function getProgressEntryCount(): ?int
    {
        return $this->progress_entry_count;
    }

    public function setProgressEntryCount(?int $progress_entry_count): static
    {
        $this->progress_entry_count = $progress_entry_count;

        return $this;
    }

    public function getProgressSolutionCount(): ?int
    {
        return $this->progress_solution_count;
    }

    public function setProgressSolutionCount(?int $progress_solution_count): static
    {
        $this->progress_solution_count = $progress_solution_count;

        return $this;
    }

    public function getUuid(): ?UuidV7
    {
        return $this->uuid;
    }

    public function setUuid(UuidV7 $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getHighscore(): ?Highscore
    {
        return $this->highscore;
    }

    public function setHighscore(Highscore $highscore): static
    {
        // set the owning side of the relation if necessary
        if ($highscore->getParticipant() !== $this) {
            $highscore->setParticipant($this);
        }

        $this->highscore = $highscore;

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(?bool $finished): static
    {
        $this->finished = $finished;

        return $this;
    }

    public function getFinishedTime(): ?int
    {
        return $this->finished_time;
    }

    public function setFinishedTime(?int $finished_time): static
    {
        $this->finished_time = $finished_time;

        return $this;
    }
}
