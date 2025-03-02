<?php

namespace App\Controller;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\CommandeType;
use Symfony\Component\Form\FormFactoryInterface;


class PanierController extends AbstractController
{
    #[Route('/panierrr', name: 'app_panier')]
public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
{
    $panier = $session->get('panier', []);
    $produitsPanier = [];

    foreach ($panier as $id => $quantite) {
        $produit = $produitRepository->find($id);
        if ($produit) {
            $produitsPanier[] = [
                'produit' => $produit,
                'quantite' => $quantite
            ];
        }
    }

    return $this->render('panier/index.html.twig', [
        'produitsPanier' => $produitsPanier
    ]);
}



#[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
public function ajouterProduit(int $id, SessionInterface $session, ProduitRepository $produitRepository): Response
{
    $panier = $session->get('panier', []); // Récupère le panier ou un tableau vide
    $produit = $produitRepository->find($id);

    if (!$produit) {
        throw $this->createNotFoundException("Le produit n'existe pas.");
    }

    if (isset($panier[$id])) {
        $panier[$id]++;
    } else {
        $panier[$id] = 1;
    }

    $session->set('panier', $panier);


    return $this->redirectToRoute('panier_afficher');
}

#[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
public function supprimerProduit(int $id, SessionInterface $session): Response
{
    $panier = $session->get('panier', []);

    if (isset($panier[$id])) {
        unset($panier[$id]);
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('panier_afficher');
}

#[Route('/panier', name: 'panier_afficher')]
public function afficherPanier(SessionInterface $session, ProduitRepository $produitRepository): Response
{
    $panier = $session->get('panier', []);
    $produitsPanier = [];

    foreach ($panier as $id => $quantite) {
        $produit = $produitRepository->find($id);
        if ($produit) {
            $produitsPanier[] = [
                'produit' => $produit,
                'quantite' => $quantite
            ];
        }
    }

    return $this->render('panier/index.html.twig', [
        'produitsPanier' => $produitsPanier
    ]);
}

#[Route('/panier/vider', name: 'panier_vider')]
public function viderPanier(SessionInterface $session): Response
{
    $session->remove('panier');
    return $this->redirectToRoute('panier_afficher');
}

#[Route('/panier/modifier/{id}/{action}', name: 'panier_modifier')]
public function modifierProduit(int $id, string $action, SessionInterface $session, ProduitRepository $produitRepository): Response
{
    $panier = $session->get('panier', []);
    $produit = $produitRepository->find($id);

    if (!$produit) {
        throw $this->createNotFoundException("Le produit n'existe pas.");
    }

    if (!isset($panier[$id])) {
        $panier[$id] = 0; // Si le produit n'est pas encore dans le panier
    }

    // Incrémenter ou décrémenter la quantité
    if ($action === 'increment') {
        $panier[$id]++;
    } elseif ($action === 'decrement' && $panier[$id] > 1) {
        $panier[$id]--;
    }

    // Sauvegarder la nouvelle quantité dans la session
    $session->set('panier', $panier);

    // Rediriger vers le panier avec un message flash
    $this->addFlash('success', 'Quantité mise à jour.');
    return $this->redirectToRoute('panier_afficher');
}



#[Route('/panier/sauvegarder', name: 'panier_sauvegarder', methods: ['POST'])]
public function sauvegarderPanier(Request $request, SessionInterface $session): JsonResponse
{
    $panier = json_decode($request->getContent(), true);

    if (!$panier) {
        return new JsonResponse(['success' => false, 'message' => 'Panier vide'], 400);
    }

    $panierAssoc = [];
    foreach ($panier as $item) {
        $panierAssoc[$item['id']] = $item['quantity'];
    }

    $session->set('panier', $panierAssoc); // Stocke le panier en session

    return new JsonResponse(['success' => true]);
}

#[Route('/panier/valider', name: 'panier_valider')]
public function indexval(SessionInterface $session, ProduitRepository $produitRepository, FormFactoryInterface $formFactory): Response
{
    $panier = $session->get('panier', []);
    $produitsPanier = [];

    foreach ($panier as $produitId => $quantite) {
        $produit = $produitRepository->find($produitId);
        if ($produit) {
            $produitsPanier[] = ['produit' => $produit, 'quantite' => $quantite];
        }
    }

    // ✅ Ne pas vider ici
    return $this->render('panier/index.html.twig', [
        'produitsPanier' => $produitsPanier,
        'formCommande' => $formFactory->create(CommandeType::class)->createView(),
        'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'] ?? 'pk_test_...',
    ]);
}




}
