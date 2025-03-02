<?php

namespace App\Repository;

use App\Entity\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserEntity>
 */
class UserEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEntity::class);
    }

    //    /**
    //     * @return UserEntity[] Returns an array of UserEntity objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserEntity
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
     /**
     * Search users by name or surname
     *
     * @param string|null $searchQuery
     * @return User[] Returns an array of User objects
     */
    public function searchByNameOrSurname(?string $searchQuery): array
    {
        if (!$searchQuery) {
            return $this->findAll();
        }

        return $this->createQueryBuilder('u')
            ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
            ->setParameter('search', '%' . $searchQuery . '%')
            ->getQuery()
            ->getResult();
    }
}
