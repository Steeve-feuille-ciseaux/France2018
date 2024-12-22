<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notableAction = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le numéro est requis')]
    #[Assert\Range(min: 1, max: 99, notInRangeMessage: 'Le numéro doit être entre {{ min }} et {{ max }}')]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La position est requise')]
    private ?string $position = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFilename = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La saison de début est requise')]
    #[Assert\Range(
        min: 1900,
        max: 2024,
        notInRangeMessage: 'La saison doit être entre {{ min }} et {{ max }}'
    )]
    private ?int $startSeason = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Expression(
        "this.getEndSeason() === null or this.getEndSeason() >= this.getStartSeason()",
        message: 'La saison de fin doit être postérieure à la saison de début'
    )]
    private ?int $endSeason = null;

    #[ORM\ManyToOne(inversedBy: 'cards', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le joueur est requis')]
    private ?Player $player = null;

    #[ORM\Column(nullable: true, options: ["default" => false])]
    private ?bool $obtenu = false;

    #[ORM\ManyToOne(inversedBy: 'cards', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function getNotableAction(): ?string
    {
        return $this->notableAction;
    }

    public function setNotableAction(string $notableAction): static
    {
        $this->notableAction = $notableAction;
        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): static
    {
        $this->imageFilename = $imageFilename;
        return $this;
    }

    public function getStartSeason(): ?int
    {
        return $this->startSeason;
    }

    public function setStartSeason(int $startSeason): static
    {
        $this->startSeason = $startSeason;
        return $this;
    }

    public function getEndSeason(): ?int
    {
        return $this->endSeason;
    }

    public function setEndSeason(?int $endSeason): static
    {
        $this->endSeason = $endSeason;
        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;
        return $this;
    }

    public function isObtenu(): ?bool
    {
        return $this->obtenu;
    }

    public function setObtenu(?bool $obtenu): static
    {
        $this->obtenu = $obtenu;
        return $this;
    }
}
