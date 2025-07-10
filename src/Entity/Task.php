<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $passKey = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $solutions = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text_before = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text_after = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?ScavangerHunt $scavangerHunt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassKey(): ?string
    {
        return $this->passKey;
    }

    public function setPassKey(string $passKey): static
    {
        $this->passKey = $passKey;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSolutions(): array
    {
        return $this->solutions;
    }

    public function setSolutions(array $solutions): static
    {
        $this->solutions = $solutions;

        return $this;
    }

    public function getTextBefore(): ?string
    {
        return $this->text_before;
    }

    public function setTextBefore(?string $text_before): static
    {
        $this->text_before = $text_before;

        return $this;
    }

    public function getTextAfter(): ?string
    {
        return $this->text_after;
    }

    public function setTextAfter(?string $text_after): static
    {
        $this->text_after = $text_after;

        return $this;
    }

    public function getScavangerHunt(): ?ScavangerHunt
    {
        return $this->scavangerHunt;
    }

    public function setScavangerHunt(?ScavangerHunt $scavangerHunt): static
    {
        $this->scavangerHunt = $scavangerHunt;

        return $this;
    }
}
