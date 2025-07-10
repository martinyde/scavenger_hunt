<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: RaceRepository::class)]
class Race
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date_point', nullable: true)]
    private ?DatePoint $timer = null;

    #[ORM\Column(nullable: true)]
    private ?int $race_duration = null;

    #[ORM\Column(nullable: true)]
    private ?array $task_access = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'race', orphanRemoval: true)]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'races')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScavangerHunt $scavenger_hunt = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimer(): ?DatePoint
    {
        return $this->timer;
    }

    public function setTimer(?DatePoint $timer): static
    {
        $this->timer = $timer;

        return $this;
    }

    public function getRaceDuration(): ?int
    {
        return $this->race_duration;
    }

    public function setRaceDuration(?int $race_duration): static
    {
        $this->race_duration = $race_duration;

        return $this;
    }

    public function getTaskAccess(): ?array
    {
        return $this->task_access;
    }

    public function setTaskAccess(?array $task_access): static
    {
        $this->task_access = $task_access;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setRace($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getRace() === $this) {
                $participant->setRace(null);
            }
        }

        return $this;
    }

    public function getScavengerHunt(): ?ScavangerHunt
    {
        return $this->scavenger_hunt;
    }

    public function setScavengerHunt(?ScavangerHunt $scavenger_hunt): static
    {
        $this->scavenger_hunt = $scavenger_hunt;

        return $this;
    }
}
