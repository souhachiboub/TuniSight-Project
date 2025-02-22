<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
#[Vich\Uploadable]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;

    #[ORM\ManyToOne(inversedBy: 'activites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */
    private $dateFin;

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * @var Collection<int, Avis>
     */

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'activite', orphanRemoval: true)]
    private Collection $avis;


    #[ORM\ManyToOne(targetEntity: Ville::class, inversedBy: 'activites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $ville = null;


    #[ORM\ManyToOne(inversedBy: 'activites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategorieActivite $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'activites')]
    private ?Offre $offre = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\ManyToMany(targetEntity: Reservation::class, inversedBy: 'activites')]
    private Collection $reservation;


    #[Vich\UploadableField(mapping: 'activite_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }



    // Ajoute un setter pour la propriété updatedAt
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // Ajoute un getter pour la propriété updatedAt
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }


    public function __construct()
    {
        $this->avis = new ArrayCollection();
        $this->ville = new ArrayCollection();
        $this->reservation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(): static
    {
        if ($this->dateDebut && $this->dateFin) {
            $interval = $this->dateDebut->diff($this->dateFin);
            $this->duree = $interval->days;
        }

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;

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
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setActivite($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getActivite() === $this) {
                $avi->setActivite(null);
            }
        }

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }


    public function getCategorie(): ?CategorieActivite
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieActivite $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): static
    {
        $this->offre = $offre;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservation(): Collection
    {
        return $this->reservation;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservation->contains($reservation)) {
            $this->reservation->add($reservation);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        $this->reservation->removeElement($reservation);

        return $this;
    }
}
