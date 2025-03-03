<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nbrStock = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'nbrProduits')]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    

    public function getNbrStock(): ?int
    {
        return $this->nbrStock;
    }

    public function setNbrStock(int $nbrStock): static
    {
        $this->nbrStock = $nbrStock;

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

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setNbrProduits($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getNbrProduits() === $this) {
                $produit->setNbrProduits(null);
            }
        }

        return $this;
    }

    
}
