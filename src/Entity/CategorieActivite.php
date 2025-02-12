<?php

namespace App\Entity;

use App\Repository\CategorieActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieActiviteRepository::class)]
class CategorieActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[Assert\NotBlank(message: "Le nom de la catégorie est requis!")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;


    #[Assert\NotBlank(message: "La description est requise!")]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, Activite>
     */
    #[ORM\OneToMany(targetEntity: Activite::class, mappedBy: 'categorie')]
    private Collection $activites;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Activite>
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): static
    {
        if (!$this->activites->contains($activite)) {
            $this->activites->add($activite);
            $activite->setCategorie($this);
        }

        return $this;
    }

    public function removeActivite(Activite $activite): static
    {
        if ($this->activites->removeElement($activite)) {
            // set the owning side to null (unless already changed)
            if ($activite->getCategorie() === $this) {
                $activite->setCategorie(null);
            }
        }

        return $this;
    }
}
