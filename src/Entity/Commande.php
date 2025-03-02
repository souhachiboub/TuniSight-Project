<?php


namespace App\Entity;


use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;


    #[ORM\Column]
    private ?int $nbrProdTotal = null;


    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserEntity $user = null;


    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', orphanRemoval: true)]
    private Collection $ligneCommandes;


    #[ORM\Column(nullable: true)]
    private ?float $prixtotale = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
        min: 2, 
        max: 100, 
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Length(
        min: 2, 
        max: 100, 
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $prenom = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse de livraison ne peut pas être vide.")]
    #[Assert\Length(
        min: 5, 
        max: 255, 
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères.",
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $adresseliv = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse e-mail ne peut pas être vide.")]
    #[Assert\Email(message: "L'adresse e-mail '{{ value }}' n'est pas valide.")]
    private ?string $email = null;


    #[ORM\Column(type: "string", length: 20, options: ["default" => "En cours"])]
    private ?string $status = "En cours";


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^\+?[0-9\s\-]{7,20}$/",
        message: "Le numéro de téléphone doit contenir entre 7 et 20 caractères, avec des chiffres, espaces ou tirets autorisés."
    )]
    private ?string $numerotelephone = null;


    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }


    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;


        return $this;
    }


    public function getNbrProdTotal(): ?int
    {
        return $this->nbrProdTotal;
    }


    public function setNbrProdTotal(int $nbrProdTotal): static
    {
        $this->nbrProdTotal = $nbrProdTotal;


        return $this;
    }


    public function getUser(): ?UserEntity
    {
        return $this->user;
    }


    public function setUser(?UserEntity $user): static
    {
        $this->user = $user;


        return $this;
    }


    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }


    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setCommande($this);
        }


        return $this;
    }


    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getCommande() === $this) {
                $ligneCommande->setCommande(null);
            }
        }


        return $this;
    }


    public function getPrixtotale(): ?float
    {
        return $this->prixtotale;
    }


    public function setPrixtotale(?float $prixtotale): static
    {
        $this->prixtotale = $prixtotale;


        return $this;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }


    public function setNom(?string $nom): static
    {
        $this->nom = $nom;


        return $this;
    }


    public function getPrenom(): ?string
    {
        return $this->prenom;
    }


    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;


        return $this;
    }


    public function getAdresseliv(): ?string
    {
        return $this->adresseliv;
    }


    public function setAdresseliv(?string $adresseliv): static
    {
        $this->adresseliv = $adresseliv;


        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(?string $email): static
    {
        $this->email = $email;


        return $this;
    }


    public function getStatus(): ?string
    {
        return $this->status;
    }
   
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
   
    public function getNumerotelephone(): ?string
    {
        return $this->numerotelephone;
    }


    public function setNumerotelephone(string $numerotelephone): static
    {
        $this->numerotelephone = $numerotelephone;


        return $this;
    }
}





