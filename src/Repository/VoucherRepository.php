<?php

namespace App\Repository;

use App\Entity\Voucher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Voucher>
 */
class VoucherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voucher::class);
    }

    public function filterVouchers(?bool $expired, ?bool $assigned)
    {
        $qb = $this->createQueryBuilder('v');
    
        if ($expired !== null) {
            if ($expired) {
                $qb->andWhere('v.dateExpiration < :now');
            } else {
                $qb->andWhere('v.dateExpiration >= :now');
            }
            $qb->setParameter('now', new \DateTime());
        }
    
        if ($assigned !== null) {
            if ($assigned) {
                $qb->andWhere('v.user IS NOT NULL');
            } else {
                $qb->andWhere('v.user IS NULL');
            }
        }
    
        return $qb->getQuery()->getResult();
    }
}
