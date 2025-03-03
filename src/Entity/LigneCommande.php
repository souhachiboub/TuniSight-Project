<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nbrProduits = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;
    

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbrProduits(): ?int
    {
        return $this->nbrProduits;
    }

    public function setNbrProduits(int $nbrProduits): static
    {
        $this->nbrProduits = $nbrProduits;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduit(): ?Produit
    {
        return $this->produit;
    }
    
    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
    
        if ($produit !== null) {
            $this->prixUnitaire = $produit->getPrix(); // Enregistre le prix du produit au moment de la commande
        }
    
        return $this;
    }
    

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getLigneCommande() === $this) {
                $produit->setLigneCommande(null);
            }
        }

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }
}
