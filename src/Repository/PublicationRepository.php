<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

//    /**
//     * @return Publication[] Returns an array of Publication objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Publication
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findBySearch(string $query)
{
    $query = mb_strtolower($query); 
    return $this->createQueryBuilder('p')
        ->leftJoin('p.user', 'u')
        ->andWhere('LOWER(p.contenu) LIKE :query OR LOWER(u.nom) LIKE :query OR LOWER(u.prenom) LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->orderBy('p.datePublication', 'DESC')
        ->getQuery()
        ->getResult();
}

}
