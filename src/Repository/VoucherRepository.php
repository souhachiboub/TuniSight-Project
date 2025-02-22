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
    
        // Si expired est défini, appliquer le filtre correspondant
        if ($expired !== null) {
            if ($expired) {
                $qb->andWhere('v.dateExpiration < :now');
            } else {
                $qb->andWhere('v.dateExpiration >= :now');
            }
            $qb->setParameter('now', new \DateTime());
        }
    
        // Si assigned est défini, appliquer le filtre correspondant
        if ($assigned !== null) {
            if ($assigned) {
                $qb->andWhere('v.user IS NOT NULL');
            } else {
                $qb->andWhere('v.user IS NULL');
            }
        }
    
        return $qb->getQuery()->getResult();
    }
    



//    /**
//     * @return Voucher[] Returns an array of Voucher objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Voucher
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
