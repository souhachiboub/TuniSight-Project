<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\CommentaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le commenataire ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $contenu = null;

   

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires',cascade:['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publication $publication = null;

    /**
     * @var Collection<int, LikesCommentaire>
     */
    #[ORM\OneToMany(targetEntity: LikesCommentaire::class, mappedBy: 'commentaire', orphanRemoval: true)]
    private Collection $likesCommentaire;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->likesCommentaire = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

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

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(?Publication $publication): static
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * @return Collection<int, LikesCommentaire>
     */
    public function getLikesCommentaire(): Collection
    {
        return $this->likesCommentaire;
    }
    public function getNbrLike(): int
{
    return count($this->likesCommentaire);
}


    public function addLikesCommentaire(LikesCommentaire $likesCommentaire): static
    {
        if (!$this->likesCommentaire->contains($likesCommentaire)) {
            $this->likesCommentaire->add($likesCommentaire);
            $likesCommentaire->setCommentaire($this);
        }

        return $this;
    }

    public function removeLikesCommentaire(LikesCommentaire $likesCommentaire): static
    {
        if ($this->likesCommentaire->removeElement($likesCommentaire)) {
            // set the owning side to null (unless already changed)
            if ($likesCommentaire->getCommentaire() === $this) {
                $likesCommentaire->setCommentaire(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
