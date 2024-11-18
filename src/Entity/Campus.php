<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'campus')]
    private Collection $users;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'campusOrganisateur')]
    private Collection $sortiesOrganisées;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->sortiesOrganisées = new ArrayCollection();
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
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCampus($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCampus() === $this) {
                $user->setCampus(null);
            }
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
            $sortiesOrganisE->setCampusOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisE(Sortie $sortiesOrganisE): static
    {
        if ($this->sortiesOrganisées->removeElement($sortiesOrganisE)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisE->getCampusOrganisateur() === $this) {
                $sortiesOrganisE->setCampusOrganisateur(null);
            }
        }

        return $this;
    }
}
