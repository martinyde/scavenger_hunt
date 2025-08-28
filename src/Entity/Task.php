<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use App\Validator\Passkey;
use App\Validator\Solutions;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?UuidV7 $uuid = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @Passkey
     */
    #[ORM\Column(length: 255)]
    private ?string $passKey = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @Solutions
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $solutions = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text_before = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text_after = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?ScavengerHunt $scavengerHunt = null;

    public function __construct()
    {
        $this->uuid = new UuidV7();
    }

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

    public function getScavengerHunt(): ?ScavengerHunt
    {
        return $this->scavengerHunt;
    }

    public function setScavengerHunt(?ScavengerHunt $scavengerHunt): static
    {
        $this->scavengerHunt = $scavengerHunt;

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
}
