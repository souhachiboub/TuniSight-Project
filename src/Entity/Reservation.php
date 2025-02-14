<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateReservation = null;

    #[ORM\Column]
    private ?int $nbrPersonnes = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

   
    #[ORM\ManyToOne(targetEntity: Activite::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activite $activite = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Voucher $voucher = null;

    #[ORM\Column]
    private ?float $totalPrice = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeInterface $dateReservation): static
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getNbrPersonnes(): ?int
    {
        return $this->nbrPersonnes;
    }

    public function setNbrPersonnes(int $nbrPersonnes): static
    {
        $this->nbrPersonnes = $nbrPersonnes;

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

    

    public function getVoucher(): ?Voucher
    {
        return $this->voucher;
    }

    public function setVoucher(?Voucher $voucher): static
    {
        $this->voucher = $voucher;

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): static
    {
        $this->activite = $activite;
        return $this;
    }

    public function getTotalPrice(): float
    {
        $total = 0;

        // Vérifier si une activité est liée à la réservation
        if ($this->activite) {
            $total += $this->activite->getPrix();
        }

        // Vérifier la validité du voucher
        if ($this->voucher !== null && !$this->voucher->getIsUsed() && $this->voucher->getDateExpiration() > new \DateTime()) {
            $discount = $this->voucher->getValeurReduction();
            $total -= ($total * ($discount / 100));
        }

        return max($total, 0);
    }

    public function applyVoucher(): float
    {
        if ($this->voucher !== null) {
            if ($this->voucher->getIsUsed()) {
                throw new \Exception("Le voucher a déjà été utilisé.");
            }

            if ($this->voucher->getDateExpiration() <= new \DateTime()) {
                throw new \Exception("Le voucher est expiré.");
            }

            $total = 0;

            if ($this->activite) {
                $total += $this->activite->getPrix();
            }

            $discount = $this->voucher->getValeurReduction();
            $total -= ($total * ($discount / 100));

            $this->voucher->setIsUsed(true);

            return max($total, 0);
        }

        return $this->activite ? $this->activite->getPrix() : 0;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }




}
