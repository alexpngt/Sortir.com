<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Nom obligatoire')]
    #[Assert\Length(
        min: 3, minMessage: "Le nom doit faire au moins 3 caractères",
        max: 180, maxMessage: "Le nom ne peut pas dépasser 180 caractères"
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Prénom obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 180,
        minMessage: "Le prénom doit faire au moins 3 caractères",
        maxMessage: "Le prénom ne peut pas dépasser 180 caractères"
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Numéro de téléphone obligatoire')]
    #[Assert\Expression(expression: "(0|\\+33|0033)[1-9][0-9]{8}")]
    #[Assert\Length(
        min: 10,
        max: 10,
        minMessage: "Le numéro de téléphone n'est pas valide",
        maxMessage: "Le numéro de téléphone n'est pas valide"
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Email obligatoire')]
    #[Assert\Email(message: 'Email non valide')]
    private ?string $email = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Mot de passe obligatoire')]
    #[Assert\Length(
        min: 8,
        max: 180,
        minMessage: "Votre mot de passe est trop court",
        maxMessage: "Votre mot de passe est trop long :c"
    )]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $admin = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'participants')]
    private Collection $sorties;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur')]
    private Collection $sortiesOrganisées;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sortiesOrganisées = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->addParticipant($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            $sorty->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisées(): Collection
    {
        return $this->sortiesOrganisées;
    }

    public function addSortiesOrganisE(Sortie $sortiesOrganisE): static
    {
        if (!$this->sortiesOrganisées->contains($sortiesOrganisE)) {
            $this->sortiesOrganisées->add($sortiesOrganisE);
            $sortiesOrganisE->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisE(Sortie $sortiesOrganisE): static
    {
        if ($this->sortiesOrganisées->removeElement($sortiesOrganisE)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisE->getOrganisateur() === $this) {
                $sortiesOrganisE->setOrganisateur(null);
            }
        }

        return $this;
    }
}
