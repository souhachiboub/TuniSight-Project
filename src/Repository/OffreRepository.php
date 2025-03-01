<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Offre>
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    public function findByExpirationStatusQuery(?bool $expired): QueryBuilder
{
    $qb = $this->createQueryBuilder('o');

    if ($expired !== null) {
        if ($expired) {
            $qb->andWhere('o.dateExpiration < :now');
        } else {
            $qb->andWhere('o.dateExpiration >= :now');
        }
        $qb->setParameter('now', new \DateTime());
    }

    return $qb; // Retourner le QueryBuilder pour la pagination
}

// public function findByExpirationStatusQuery(?bool $expired)
// {
//     $qb = $this->createQueryBuilder('o');
//     if ($expired !== null) {
//         $qb->andWhere('o.dateExpiration ' . ($expired ? '<' : '>=') . ' CURRENT_DATE()');
//     }
//     return $qb;
// }

    // public function findByExpirationStatusQuery(?string $expirée): QueryBuilder
    // {
    //     $qb = $this->createQueryBuilder('o');

    //     if ($expirée === 'expirée') {
    //         // Filtrer pour les offres expirées
    //         $qb->andWhere('o.dateExpiration < :now')
    //             ->setParameter('now', new \DateTime());
    //     } elseif ($expirée === 'non_expirée') {
    //         // Filtrer pour les offres non expirées
    //         $qb->andWhere('o.dateExpiration >= :now')
    //             ->setParameter('now', new \DateTime());
    //     }

    //     return $qb;
    // }

//    /**
//     * @return Offre[] Returns an array of Offre objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Offre
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
