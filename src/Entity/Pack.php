<?php

namespace App\Entity;

use App\Repository\PackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackRepository::class)]
class Pack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'packs')]
    private Collection $produits;  // Changed the name to 'produits' to follow the plural convention

    #[ORM\ManyToOne(inversedBy: 'packs')]
    private ?Offre $offre = null;

    #[ORM\Column]
    private ?float $reductionTotal = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();  // Adjusted to 'produits'
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

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection // Adjusted getter method to 'getProduits'
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);  // Adjusted to 'produits'
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        $this->produits->removeElement($produit);  // Adjusted to 'produits'

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

    public function calculerReductionTotale(): float
    {
        $totalReduction = 0;
        foreach ($this->produits as $produit) {  // Adjusted to 'produits'
            $prixProduit = $produit->getPrix();
            $reductionProduit = ($prixProduit * $this->offre->getReduction()) / 100;
            $totalReduction += $reductionProduit;
        }

        return $totalReduction;
    }

    public function getReductionTotal(): ?float
    {
        return $this->reductionTotal;
    }

    public function setReductionTotal(float $reductionTotal): static
    {
        $this->reductionTotal = $reductionTotal;

        return $this;
    }

    public function calculerReduction(): float
    {
        $totalReduction = $this->reductionTotal;
        foreach ($this->produits as $produit) { 
            $totalReduction += $produit->getPrix() * ($this->reductionTotal / 100);
        }
        return $totalReduction;
    }
}
