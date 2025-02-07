<?php

namespace App\Entity;

use App\Enum\TypeReduction;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VoucherRepository;

#[ORM\Entity(repositoryClass: VoucherRepository::class)]
class Voucher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeVoucher = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateEmission = null;

   
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(length: 255)]
    private ?string $valeurReduction = null;

    #[ORM\OneToOne(mappedBy: 'voucher', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isUsed = null;
    public function __construct()
    {
         $this->codeVoucher = Uuid::v4()->toRfc4122(); // Générer un code unique aléatoire
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeVoucher(): ?string
    {
        return $this->codeVoucher;
    }

    public function setCodeVoucher(string $codeVoucher): static
    {
        $this->codeVoucher = $codeVoucher;

        return $this;
    }

    public function getDateEmission(): ?\DateTimeInterface
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTimeInterface $dateEmission): static
    {
        $this->dateEmission = $dateEmission;

        return $this;
    }
    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function getValeurReduction(): ?string
    {
        return $this->valeurReduction;
    }

    public function setValeurReduction(string $valeurReduction): static
    {
        $this->valeurReduction = $valeurReduction;

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
            $this->user->setVoucher(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getVoucher() !== $this) {
            $user->setVoucher($this);
        }

        $this->user = $user;

        return $this;
    }
    public function getIsUsed(): ?bool
    {
        return $this->isUsed;
    }
    

    public function setIsUsed(?bool $isUsed): static
{
    $this->isUsed = $isUsed;
    return $this;
}

}
