<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Form\StockType;
use App\Repository\ProduitRepository;
use App\Repository\CategorieProduitRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\UserEntity;
use WebSocket\Client;




class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(ProduitRepository $produitRepository): Response
    {
        // Récupération des produits existants
        $produits = $produitRepository->findAll();
    
        // Création d'une instance vide de Produit pour le formulaire
        $produit = new Produit();
        // Création du formulaire à partir du ProduitType
        $formProduit = $this->createForm(ProduitType::class, $produit, ['is_edit' => false]);
    
        // Transmission des produits et du formulaire (sous forme de vue) au template
        return $this->render('produit/index.html.twig', [
            'produits'    => $produits,
            'formProduit' => $formProduit->createView(),
        ]);
    }




    
    #[Route('/produit/create', name: 'produit_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager , Security $security): JsonResponse
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setUser($security->getUser());


            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $produit->setImageFile($imageFile);
            }
            $produit->setDisponibilite($produit->getQuantite() > 0);
            error_log('Début création du produit : ' . $produit->getLibelle());

    
            $entityManager->persist($produit);
            $entityManager->flush();
            error_log('Produit créé: ' . $produit->getLibelle() . ' avec l\'ID: ' . $produit->getId());

    
            return new JsonResponse([
                'success' => true,
                'message' => 'Produit créé avec succès'
            ]);
        }
    
        // 🔹 Récupération des erreurs par champ
        $errors = [];
        foreach ($form->all() as $fieldName => $formField) {
            foreach ($formField->getErrors() as $error) {
                $errors[$fieldName][] = $error->getMessage();
            }
        }
    
        return new JsonResponse([
            'success' => false,
            'errors' => $errors
        ], Response::HTTP_BAD_REQUEST);
    }
    

    
    #[Route('/produit/{id}', name: 'produit_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(ProduitRepository $produitRepository, int $id): JsonResponse
    {
        $produit = $produitRepository->find($id);
    
        if (!$produit) {
            return $this->json(['success' => false, 'message' => 'Produit non trouvé.'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json([
            'success' => true,
            'produit' => [
                'libelle' => $produit->getLibelle(),
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix(),
                'categorie' => $produit->getCategorieProduit()->getNom(), // Assure-toi que getNom() existe
                'quantite' => $produit->getQuantite(),
                'image' => $produit->getImageFile() ? '/uploads/produits' . $produit->getImageFile() : null,
            ]
        ]);
    }
    


    #[Route('/produit/{id}/edit', name: 'produit_edit')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(ProduitType::class, $produit, ['is_edit' => true]);
        $form->handleRequest($request);
    
        // Si le formulaire est soumis (POST)
        if ($form->isSubmitted()) {
            // Si le formulaire est valide
            if ($form->isValid()) {
                // Validation supplémentaire
                $errors = $validator->validate($produit);
                if (count($errors) > 0) {
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[] = $error->getMessage();
                    }
                    return new JsonResponse(['success' => false, 'message' => implode("\n", $errorMessages)]);
                }
    
                // Gestion de l'upload de l'image
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile && $imageFile->isFile()) {
                    $oldImage = $produit->getImage();
                    if ($oldImage) {
                        $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/produits/' . $oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/produits/',
                        $newFilename
                    );
                    $produit->setImage($newFilename);
                }
    
                $em->flush();
    
                // Retourner une réponse JSON en cas de succès
                $this->addFlash('success', 'Produit modifié avec succès !');

                // Retourner une réponse JSON en cas de succès
                return new JsonResponse(['success' => true, 'message' => 'Produit modifié avec succès !']);
            } else {
                $errorMessages = [];
                foreach ($form->getErrors(true) as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'message' => implode("\n", $errorMessages)
                ]);
            
            }
        }
    
        // Pour le chargement initial (GET), renvoyer le formulaire partiel
        return $this->render('produit/_edit_form.html.twig', [
            'form'    => $form->createView(),
            'produit' => $produit
        ]);
    }



    
    #[Route('/produit/{id}/delete', name: 'produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
    
            return new JsonResponse(['success' => true ,
            
        
        ]);
            
        }
    
        return new JsonResponse(['success' => false, 'message' => 'Jeton CSRF invalide'], 400); }





    #[Route('/modifier-quantite/{id}', name: 'modifier_quantite', methods: ['POST'])]
public function modifierQuantite(Request $request, Produit $produit, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
{
    // Récupérer les données envoyées en JSON
    $data = json_decode($request->getContent(), true);

    // Vérifier si 'quantite' est présente et valide
    if (!isset($data['quantite']) || $data['quantite'] === '') {
        return new JsonResponse([
            'success' => false,
            'message' => "La quantité est obligatoire."
        ], 400);
    }

    // Mettre à jour la quantité du produit
    $quantite = (int) $data['quantite'];
    $produit->setQuantite($quantite);

    // Validation de l'entité Produit (les erreurs de validation seront directement issues des annotations de l'entité)
    $errors = $validator->validate($produit);

    // Si des erreurs de validation sont trouvées, renvoyer un message d'erreur
    if (count($errors) > 0) {
        // Récupérer les messages d'erreur directement depuis l'entité
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage(); // Utilise directement les messages d'erreur définis dans l'entité
        }
        return new JsonResponse([
            'success' => false,
            'message' => implode(', ', $errorMessages) // Renvoie les messages d'erreur récupérés
        ], 400);
    }

    // Sauvegarde dans la base de données si tout est valide
    $entityManager->flush();



    return new JsonResponse([
        'success' => true,
        'message' => "Quantité modifiée avec succèsss !"
    ]);
}

    
    
    


#[Route('/boutiqueuser', name: 'app_boutiqueuser')]
public function boutiqueuser(Security $security): Response
{
    $user = $security->getUser(); // Récupère l'utilisateur connecté

    return $this->render('baseboutique.html.twig', [
        'user' => $user, // Passe l'utilisateur à la vue
    ]);
}


#[Route('/boutique', name: 'app_boutique')]
public function boutique(ProduitRepository $produitRepository, CategorieProduitRepository $categorieproduitRepository, Request $request): Response
{


    // Récupérer toutes les catégories pour le filtre
    $categories = $categorieproduitRepository->findAll();

    // Récupérer les filtres (catégorie, recherche, plage de prix)
    $selectedCategorie = $request->query->get('category');
    $searchQuery = $request->query->get('search');

    $minPrice = $request->query->get('minPrice');
    $maxPrice = $request->query->get('maxPrice');
    $minPrice = !empty($minPrice) ? (float)$minPrice : null;
    $maxPrice = !empty($maxPrice) ? (float)$maxPrice : null;

    // Récupérer les produits en fonction des filtres
    $queryBuilder = $produitRepository->createQueryBuilder('p');

    if ($selectedCategorie) {
        $queryBuilder->andWhere('p.CategorieProduit = :categorie')
                     ->setParameter('categorie', $selectedCategorie);
    }

    if ($searchQuery) {
        $queryBuilder->andWhere('p.libelle LIKE :search OR p.description LIKE :search')
                     ->setParameter('search', '%' . $searchQuery . '%');
    }

    if ($minPrice !== null) {
        $queryBuilder->andWhere('p.prix >= :minPrice')
                     ->setParameter('minPrice', $minPrice);
    }

    if ($maxPrice !== null) {
        $queryBuilder->andWhere('p.prix <= :maxPrice')
                     ->setParameter('maxPrice', $maxPrice);
    }

    $products = $queryBuilder->getQuery()->getResult();

    return $this->render('base/indexboutique.html.twig', [
        'products' => $products,
        'categories' => $categories,
        'selectedCategory' => $selectedCategorie,
        'searchQuery' => $searchQuery,
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice,
    ]);
}

























#[Route('/produit/delete-multiple', name: 'produit_delete_multiple', methods: ['POST'])]
public function deleteMultiple(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $em): Response
{
    $data = json_decode($request->getContent(), true);
    $ids = $data['ids'] ?? [];

    if (!empty($ids)) {
        $produits = $produitRepository->findBy(['id' => $ids]);

        foreach ($produits as $produit) {
            $em->remove($produit);
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

    return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
}









#[Route('/boutique/{id}', name: 'afficherproduit')]
     
    public function afficher(ProduitRepository $produitRepository, int $id): Response
    {
        // Récupère le produit depuis la base de données
        $produit = $produitRepository->find($id);

        // Si le produit n'existe pas, renvoie une erreur 404
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Passe le produit à la vue
        return $this->render('produit/detailproduit.html.twig', [
            'produit' => $produit,
        ]);
    }




    public function checkStockAndNotify(Produit $produit)
    {
        error_log("🟡 Vérification du stock pour " . $produit->getLibelle() . " (Quantité: " . $produit->getQuantite() . ")");
    
        if ($produit->getQuantite() <= 2) {
            try {
                error_log("🟠 Stock bas détecté pour " . $produit->getLibelle());
    
                $client = new \WebSocket\Client("ws://localhost:9090");
                $client->send("⚠️ Stock bas : " . $produit->getLibelle());
                $client->close();
    
                error_log("✅ Message envoyé avec succès !");
            } catch (\Exception $e) {
                error_log("❌ Erreur WebSocket : " . $e->getMessage());
            }
        }
    }
    
    
}

