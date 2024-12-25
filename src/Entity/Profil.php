<?php

namespace App\Entity;

use App\Repository\ProfilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProfilRepository::class)]
class Profil implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 25)]
    private ?string $pseudo = null;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 4)]
    private int $role = 1;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'signature', targetEntity: Card::class)]
    private Collection $signatures;

    #[ORM\OneToMany(mappedBy: 'profil', targetEntity: Card::class)]
    private Collection $cards;

    public function __construct()
    {
        $this->signatures = new ArrayCollection();
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function setRole(int $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->pseudo;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    public function addSignature(Card $signature): static
    {
        if (!$this->signatures->contains($signature)) {
            $this->signatures->add($signature);
            $signature->setSignature($this);
        }
        return $this;
    }

    public function removeSignature(Card $signature): static
    {
        if ($this->signatures->removeElement($signature)) {
            if ($signature->getSignature() === $this) {
                $signature->setSignature(null);
            }
        }
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
            $card->setProfil($this);
        }
        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            if ($card->getProfil() === $this) {
                $card->setProfil(null);
            }
        }
        return $this;
    }
}
