<?php

namespace App\Entity;

use App\Enum\TypeReduction;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VoucherRepository;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: VoucherRepository::class)]
class Voucher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(['message' => 'Le code du voucher ne peut pas être vide.'])]
    private ?string $codeVoucher = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateEmission = null;

    #[Assert\GreaterThanOrEqual(
        propertyPath: "dateEmission",
        message: "La date d'expiration doit être supérieure ou égale à la date d'émission."
    )]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
   
    #[Assert\GreaterThanOrEqual(value: 5, message: "La valeur de réduction doit être positive.")]
    private ?int $valeurReduction = null;

    #[ORM\ManyToOne(inversedBy: 'vouchers')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isUsed = null;

    
    public function __construct()
    {
         $this->codeVoucher = Uuid::v4()->toRfc4122(); 
        
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

    public function getValeurReduction(): ?int
    {
        return $this->valeurReduction;
    }

    public function setValeurReduction(int $valeurReduction): static
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
        // if ($user === null && $this->user !== null) {
        //     $this->user->setVoucher(null);
        // }

        // if ($user !== null && $user->getVoucher() !== $this) {
        //     $user->setVoucher($this);
        // }

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