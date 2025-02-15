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



    #[Route('/produit/{id}', name: 'app_categorie_produit_show', methods: ['GET'])]
    public function show(CategorieProduit $categorie): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'categorie' => [
                'nom' => $categorie->getNom(),
                'description' => $categorie->getDescription(),
            ]
        ]);
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
     #[Route('/delete-multiple', name: 'app_categorie_produit_delete_multiple')]
     public function deleteMultiple(Request $request, EntityManagerInterface $em): Response
     {
         $data = json_decode($request->getContent(), true);
         $ids = $data['ids'] ?? [];
     
         if (empty($ids)) {
             $this->addFlash('error', 'Aucune catégorie sélectionnée.');
             return $this->redirectToRoute('app_categorie_produit');
         }
     
         $categories = $em->getRepository(CategorieProduit::class)->findBy(['id' => $ids]);
     
         foreach ($categories as $categorie) {
             $em->remove($categorie);
         }
     
         $em->flush();
         
         return new JsonResponse(['success' => true, 'message' => count($categories) . ' catégorie(s) supprimée(s) avec succès.']);
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









































    #[Route('/produit/new', name: 'app_categorie_produit_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $categorie = new CategorieProduit();
        $categorie->setNom($data['nom'] ?? '');
        $categorie->setDescription($data['description'] ?? '');
    
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
    
  

















    #[Route('/produit/{id}/edit', name: 'app_categorie_produit_edit', methods: ['PUT'])]
    public function edit(Request $request, CategorieProduit $categorie, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        // Décoder les données JSON de la requête
        $data = json_decode($request->getContent(), true);
        
        // Mettre à jour les propriétés de la catégorie
        $categorie->setNom($data['nom'] ?? '');
        $categorie->setDescription($data['description'] ?? '');
    
        // Valider l'entité
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
    
    
       
    
    
        // Enregistrer les modifications en base de données
        $em->flush();
    
        return new JsonResponse([
            'success' => true,
            'message' => 'Catégorie modifiée avec succès.'
        ]);
    }
    
}












