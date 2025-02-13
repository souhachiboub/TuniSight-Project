<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Form\StockType;
use App\Repository\ProduitRepository;
use App\Repository\CategorieProduitRepository;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,

        ]);
    }

    #[Route('/produit/new', name: 'produit_create')]
public function create(Request $request, EntityManagerInterface $em): Response
{
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit, ['is_edit' => false]);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload de l'image
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            $produit->setImageFile($imageFile);
        }

        $em->persist($produit);
        $em->flush();
        $this->addFlash('success', 'Le produit a été créé avec succès.');

        return $this->redirectToRoute('produit_create');
    }

    return $this->render('produit/create.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/produit/{id}', name: 'produit_show', requirements: ['id' => '\d+'])]
public function show(ProduitRepository $produitRepository, int $id): Response
{
    $produit = $produitRepository->find($id);
    
    if (!$produit) {
        throw $this->createNotFoundException('Produit non trouvé.');
    }
    
    return $this->render('produit/show.html.twig', [
        'produit' => $produit,
    ]);
}



    #[Route('/produit/{id}/edit', name: 'produit_edit')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $produit, ['is_edit' => true]);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) { // Vérifie si une nouvelle image est envoyée
                if (file_exists($imageFile->getPathname())) { // Vérifie si le fichier temporaire existe
                    // Supprimer l'ancienne image si elle existe
                    $oldImage = $produit->getImage();
                    if ($oldImage) {
                        $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/produits/' . $oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
    
                    // Générer un nom de fichier unique
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
    
                    // Déplacer le fichier vers le répertoire de stockage
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/produits/',
                        $newFilename
                    );
    
                    // Mettre à jour le champ image de l'entité
                    $produit->setImage($newFilename);
                } else {
                    $this->addFlash('error', 'Le fichier temporaire est introuvable.');
                }
            }
    
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
    
            // Redirection vers la liste des produits après la modification
        }
    
        // Afficher le formulaire si la requête n'est pas soumise ou si le formulaire n'est pas valide
        return $this->render('produit/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/{id}/delete', name: 'produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
        }

        return $this->redirectToRoute('app_produit');
    }

    #[Route("/produit/{id}/stock", name: "produit_edit_stock", methods: ['GET', 'POST'])]
public function editStock(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(StockType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        
        // Ajouter un indicateur de succès
        return $this->redirectToRoute('produit_edit_stock', ['id' => $produit->getId(), 'success' => 1]);
    }

    return $this->render('produit/edit_stock.html.twig', [
        'form' => $form->createView(),
        'produit' => $produit,
        'success' => $request->query->get('success', 0), // Vérifier si succès existe
    ]);
}



    #[Route('/boutique', name: 'app_boutique')]
public function boutique(ProduitRepository $produitRepository, CategorieProduitRepository $categorieproduitRepository, Request $request): Response
{
    // Récupérer toutes les catégories pour le filtre
    $categories = $categorieproduitRepository->findAll();

    // Récupérer le filtre de catégorie et la recherche (s'ils existent)
    $selectedCategorie = $request->query->get('category');
    $searchQuery = $request->query->get('search');

    // Récupérer les produits en fonction des filtres
    $queryBuilder = $produitRepository->createQueryBuilder('p');

    if ($selectedCategorie) {
        $queryBuilder->andWhere('p.categorie = :categorie')
                     ->setParameter('categorie', $selectedCategorie);
    }

    if ($searchQuery) {
        $queryBuilder->andWhere('p.libelle LIKE :search OR p.description LIKE :search')
                     ->setParameter('search', '%' . $searchQuery . '%');
    }

    $products = $queryBuilder->getQuery()->getResult();

    return $this->render('boutique.html.twig', [
        'products' => $products,
        'categories' => $categories,
        'selectedCategory' => $selectedCategorie,
        'searchQuery' => $searchQuery
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
















}  