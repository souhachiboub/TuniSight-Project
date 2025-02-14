<?php
namespace App\Service;

use App\Entity\Voucher;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;

class VoucherService
{
    private EntityManagerInterface $entityManager;
    private $voucherRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function applyVoucher(Reservation $reservation, Voucher $voucher): bool
    {
   
        if ($voucher->getIsUsed()) {
            return false; 
        }

        if (new \DateTime() > $voucher->getDateExpiration()) {
            return false; 
        }

        
        $totalPrix = 0;
        foreach ($reservation->getActivite() as $activite) {
            $totalPrix += $activite->getPrix();
        }

        
        $reduction = $voucher->getValeurReduction();
        $totalPrix -= $reduction;
        $totalPrix = max($totalPrix, 0); 

        
        $voucher->setIsUsed(true);
        $reservation->setVoucher($voucher);

        
        $this->entityManager->persist($voucher);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return true;
    }
    public function getVoucherByCode(string $voucherCode): ?Voucher
    {
        return $this->voucherRepository->findOneBy(['codeVoucher' => $voucherCode]);
    }
}
