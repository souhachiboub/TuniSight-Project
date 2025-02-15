<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]

class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le libellé ne peut pas être vide ririiii.", groups: ['Default'])]
    #[Assert\Length(
    max: 255,
    maxMessage: "Le libellé ne peut pas dépasser 255 caractères."
)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser 255 caractères."
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix est obligatoire.")]
#[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif rourorrrrr.")]
    private ?float $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;
    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png"],
        mimeTypesMessage: "Seules les images JPG et PNG sont autorisées.",
        maxSizeMessage: "L'image ne doit pas dépasser 2 Mo."
    )]
    #[Vich\UploadableField(mapping: 'produit_image', fileNameProperty: 'image')]
    private ?File $imageFile = null; // Ce champ est virtuel, il ne correspond pas à un attribut dans la base de données


    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Vous devez choisir une catégorie.")]

    private ?CategorieProduit $CategorieProduit = null;

    #[ORM\ManyToMany(targetEntity: Panier::class, inversedBy: 'produits')]
    private Collection $listProduits;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Stock $nbrProduits = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?LigneCommande $ligneCommande = null;

    #[ORM\Column]
    #[Assert\NotNull(message: " rawouna La quantité est obligatoire.")]
    #[Assert\PositiveOrZero(message: "  rawouna La quantité doit être supérieure ou égale à 0.")]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;

    #[ORM\Column(length: 255)]
    private ?string $Reference = null;

    public function __construct()
    {
        $this->listProduits = new ArrayCollection();
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
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



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCategorieProduit(): ?CategorieProduit
    {
        return $this->CategorieProduit;
    }
    
    public function setCategorieProduit(?CategorieProduit $CategorieProduit): static
    {
        $this->CategorieProduit = $CategorieProduit;
        return $this;
    }
    

    public function getListProduits(): Collection
    {
        return $this->listProduits;
    }

    public function addListProduit(Panier $listProduit): static
    {
        if (!$this->listProduits->contains($listProduit)) {
            $this->listProduits->add($listProduit);
        }
        return $this;
    }

    public function removeListProduit(Panier $listProduit): static
    {
        $this->listProduits->removeElement($listProduit);
        return $this;
    }

    public function getNbrProduits(): ?Stock
    {
        return $this->nbrProduits;
    }

    public function setNbrProduits(?Stock $nbrProduits): static
    {
        $this->nbrProduits = $nbrProduits;
        return $this;
    }

    public function getLigneCommande(): ?LigneCommande
    {
        return $this->ligneCommande;
    }

    public function setLigneCommande(?LigneCommande $ligneCommande): static
    {
        $this->ligneCommande = $ligneCommande;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
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
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
  
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getReference(): ?string
    {
        return $this->Reference;
    }

    public function setReference(string $Reference): static
    {
        $this->Reference = $Reference;

        return $this;
    }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateRef(): void
    {
        if ($this->getCategorieProduit() !== null) {
            // Récupère le nom de la catégorie et en extrait les 3 premières lettres en minuscules
            $catName = $this->getCategorieProduit()->getNom();
            $prefix = substr(strtolower($catName), 0, 3);
            
            // Génère un nombre aléatoire sur 5 chiffres (entre 10000 et 99999)
            $number = random_int(10000, 99999);
            
            // Construit la référence au format "c [préfixe][nombre]"
            $this->setReference('c ' . $prefix . $number);
        }
    }
    
    public function isDisponible(): bool
    {
        return $this->quantite > 0;
    }
    
}
