<?php

namespace App\Controller;

use App\Entity\CategorieProduit;
use App\Repository\CategorieProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/categorie')]
class CategorieProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_categorie_produit', methods: ['GET'])]
    public function index(CategorieProduitRepository $repo): Response
    {
        $categories = $repo->findAll();

        return $this->render('categorie_produit/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/produit/new', name: 'app_categorie_produit_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Création et affectation des valeurs
        $categorie = new CategorieProduit();
        $categorie->setNom($data['nom'] ?? '');
        $categorie->setDescription($data['description'] ?? '');

        // Validation de l'entité
        $errors = $validator->validate($categorie);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => implode("\n", $errorMessages)
            ], 400);
        }

        $em->persist($categorie);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Catégorie ajoutée avec succès.']);
    }

   

    #[Route('/produit/{id}/delete', name: 'app_categorie_produit_delete', methods: ['DELETE'])]
    public function delete(CategorieProduit $categorie, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($categorie);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Catégorie supprimée avec succès.']);
    }

    /**
     * Suppression multiple de catégories.
     * Attend dans le corps de la requête JSON un tableau d'IDs, par exemple : { "ids": [1, 2, 3] }
     */
    #[Route('/produit/delete-multiple', name: 'app_categorie_produit_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(Request $request, CategorieProduitRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Liste des IDs manquante ou invalide.'
            ], 400);
        }

        foreach ($data['ids'] as $id) {
            $categorie = $repo->find($id);
            if ($categorie) {
                $em->remove($categorie);
            }
        }
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Catégories supprimées avec succès.']);
    }

    #[Route('/produit/{id}/view', name: 'app_categorie_produit_view', methods: ['GET'])]
    public function view(CategorieProduit $categorie): JsonResponse
    {
        // Vous pouvez adapter les données renvoyées si nécessaire
        return new JsonResponse([
            'nom' => $categorie->getNom(),
            'description' => $categorie->getDescription(),
        ]);
    }
}


