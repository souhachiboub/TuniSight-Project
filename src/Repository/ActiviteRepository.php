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
     * @param string|null $categorieId ID de la catégorie (optionnel, peut être une chaîne vide pour "Toutes les catégories")
     * @param string|null $villeId ID de la ville (optionnel, peut être une chaîne vide pour "Toutes les villes")
     * @param float|null $prixMin Prix minimal (optionnel)
     * @param float|null $prixMax Prix maximum (optionnel)
     * @return Activite[] Retourne un tableau d'activités filtrées
     */
    public function findByFilters(?string $categorieId = null, ?string $villeId = null, ?float $prixMin = null, ?float $prixMax = null): array
    {
        // Crée un QueryBuilder pour l'entité Activite
        $qb = $this->createQueryBuilder('a');

        // Filtre par catégorie si un ID de catégorie est fourni et non vide
        if ($categorieId !== null && $categorieId !== '') {
            $qb->join('a.categorie', 'c') // Jointure avec l'entité Categorie
                ->andWhere('c.id = :categorieId') // Filtre sur l'ID de la catégorie
                ->setParameter('categorieId', (int) $categorieId); // Convertit en entier
        }

        // Filtre par ville si un ID de ville est fourni et non vide
        if ($villeId !== null && $villeId !== '') {
            $qb->join('a.ville', 'v') // Jointure avec l'entité Ville
                ->andWhere('v.id = :villeId') // Filtre sur l'ID de la ville
                ->setParameter('villeId', (int) $villeId); // Convertit en entier
        }

        // Filtre par prix minimal si un prix est fourni
        if ($prixMin !== null) {
            $qb->andWhere('a.prix >= :prixMin') // Filtre sur le prix minimal
                ->setParameter('prixMin', $prixMin);
        }

        // Filtre par prix maximum si un prix est fourni
        if ($prixMax !== null) {
            $qb->andWhere('a.prix <= :prixMax') // Filtre sur le prix maximal
                ->setParameter('prixMax', $prixMax);
        }

        // Retourne les résultats de la requête
        return $qb->getQuery()->getResult();
    }
}
