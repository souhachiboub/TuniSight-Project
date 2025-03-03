<?php

namespace App\Repository;

use App\Entity\Activite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activite>
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    //    /**
    //     * @return Activite[] Returns an array of Activite objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Activite
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Filtre les activités par catégorie, ville, prix minimal et prix maximum.
     *
     * @param string|null 
     * @param string|null 
     * @param float|null 
     * @param float|null
     * @return Activite[] 
     */
    public function findByFilters(?string $categorieId = null, ?string $villeId = null, ?float $prixMin = null, ?float $prixMax = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($categorieId !== null && $categorieId !== '') {
            $qb->join('a.categorie', 'c')
                ->andWhere('c.id = :categorieId')
                ->setParameter('categorieId', (int) $categorieId);
        }

        if ($villeId !== null && $villeId !== '') {
            $qb->join('a.ville', 'v')
                ->andWhere('v.id = :villeId')
                ->setParameter('villeId', (int) $villeId);
        }

        if ($prixMin !== null) {
            $qb->andWhere('a.prix >= :prixMin')
                ->setParameter('prixMin', $prixMin);
        }

        if ($prixMax !== null) {
            $qb->andWhere('a.prix <= :prixMax')
                ->setParameter('prixMax', $prixMax);
        }

        return $qb->getQuery()->getResult();
    }
}
