<?php

namespace App\Entity;

use App\Repository\LikesCommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikesCommentaireRepository::class)]
class LikesCommentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

  

    #[ORM\ManyToOne(inversedBy: 'likesCommmentaire')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'likesCommentaire')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commentaire $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCommentaire(): ?Commentaire
    {
        return $this->commentaire;
    }

    public function setCommentaire(?Commentaire $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
