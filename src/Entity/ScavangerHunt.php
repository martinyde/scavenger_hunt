<?php

namespace App\Entity;

use App\Repository\ScavangerHuntRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScavangerHuntRepository::class)]
class ScavangerHunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'scavangerHunt')]
    private Collection $tasks;

    /**
     * @var Collection<int, Race>
     */
    #[ORM\OneToMany(targetEntity: Race::class, mappedBy: 'scavenger_hunt', orphanRemoval: true)]
    private Collection $races;

    #[ORM\ManyToOne(inversedBy: 'scavangerHunts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Highscore>
     */
    #[ORM\OneToMany(targetEntity: Highscore::class, mappedBy: 'scavenger_hunt')]
    private Collection $highscores;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->races = new ArrayCollection();
        $this->highscores = new ArrayCollection();
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

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setScavangerHunt($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getScavangerHunt() === $this) {
                $task->setScavangerHunt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Race>
     */
    public function getRaces(): Collection
    {
        return $this->races;
    }

    public function addRace(Race $race): static
    {
        if (!$this->races->contains($race)) {
            $this->races->add($race);
            $race->setScavengerHunt($this);
        }

        return $this;
    }

    public function removeRace(Race $race): static
    {
        if ($this->races->removeElement($race)) {
            // set the owning side to null (unless already changed)
            if ($race->getScavengerHunt() === $this) {
                $race->setScavengerHunt(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Highscore>
     */
    public function getHighscores(): Collection
    {
        return $this->highscores;
    }

    public function addHighscore(Highscore $highscore): static
    {
        if (!$this->highscores->contains($highscore)) {
            $this->highscores->add($highscore);
            $highscore->setScavengerHunt($this);
        }

        return $this;
    }

    public function removeHighscore(Highscore $highscore): static
    {
        if ($this->highscores->removeElement($highscore)) {
            // set the owning side to null (unless already changed)
            if ($highscore->getScavengerHunt() === $this) {
                $highscore->setScavengerHunt(null);
            }
        }

        return $this;
    }
}
