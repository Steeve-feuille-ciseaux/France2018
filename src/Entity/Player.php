<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $position = null;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 99)]
    private ?int $jerseyNumber = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $currentClub = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $nationality = null;

    #[ORM\Column]
    private ?int $worldCups = 0;

    #[ORM\Column]
    private ?int $championsLeague = 0;

    #[ORM\Column]
    private ?int $europeLeague = 0;

    #[ORM\Column]
    private ?int $nationalChampionship = 0;

    #[ORM\Column]
    private ?int $nationalCup = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoFilename = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Card::class, orphanRemoval: true)]
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
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

    public function getJerseyNumber(): ?int
    {
        return $this->jerseyNumber;
    }

    public function setJerseyNumber(int $jerseyNumber): static
    {
        $this->jerseyNumber = $jerseyNumber;
        return $this;
    }

    public function getCurrentClub(): ?string
    {
        return $this->currentClub;
    }

    public function setCurrentClub(string $currentClub): static
    {
        $this->currentClub = $currentClub;
        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): static
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getWorldCups(): ?int
    {
        return $this->worldCups;
    }

    public function setWorldCups(int $worldCups): static
    {
        $this->worldCups = $worldCups;
        return $this;
    }

    public function getChampionsLeague(): ?int
    {
        return $this->championsLeague;
    }

    public function setChampionsLeague(int $championsLeague): static
    {
        $this->championsLeague = $championsLeague;
        return $this;
    }

    public function getEuropeLeague(): ?int
    {
        return $this->europeLeague;
    }

    public function setEuropeLeague(int $europeLeague): static
    {
        $this->europeLeague = $europeLeague;
        return $this;
    }

    public function getNationalChampionship(): ?int
    {
        return $this->nationalChampionship;
    }

    public function setNationalChampionship(int $nationalChampionship): static
    {
        $this->nationalChampionship = $nationalChampionship;
        return $this;
    }

    public function getNationalCup(): ?int
    {
        return $this->nationalCup;
    }

    public function setNationalCup(int $nationalCup): static
    {
        $this->nationalCup = $nationalCup;
        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): static
    {
        $this->photoFilename = $photoFilename;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setPlayer($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getPlayer() === $this) {
                $card->setPlayer(null);
            }
        }

        return $this;
    }
}
