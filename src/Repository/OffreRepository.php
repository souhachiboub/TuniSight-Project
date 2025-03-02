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
   
    public function findByExpirationStatus(int $expired)
{
    $qb = $this->createQueryBuilder('o');

    if ($expired === 1) {
        $qb->where('o.dateExpiration < :now');
    } elseif ($expired === 0) {
        $qb->where('o.dateExpiration >= :now');
    }

    return $qb->setParameter('now', new \DateTime())
              ->getQuery()
              ->getResult();
}

}
