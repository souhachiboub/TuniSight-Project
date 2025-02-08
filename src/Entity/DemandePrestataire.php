<?php

namespace App\Entity;

use App\Enum\EtatDemande;
use App\Repository\DemandePrestataireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: DemandePrestataireRepository::class)]
class DemandePrestataire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(length: 255)]
    private EtatDemande $etat;

    #[ORM\OneToOne(mappedBy: 'demandePrestataire', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getEtat(): EtatDemande
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setDemandePrestataire(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getDemandePrestataire() !== $this) {
            $user->setDemandePrestataire($this);
        }

        $this->user = $user;

        return $this;
    }
}
