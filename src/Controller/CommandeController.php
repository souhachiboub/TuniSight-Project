<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\CommandeRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface; 

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(): Response
    {
        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
        ]);
    }

    #[Route('/admin/cmd', name: 'admin_commandes')]
    public function indexcmd(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();

        return $this->render('commande/admin.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/admin/commande/delete/{id}', name: 'admin_delete_commande', methods: ['DELETE'])]
    public function delete(Commande $commande, EntityManagerInterface $entityManager, Request $request): JsonResponse
    { 
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Requête invalide'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($commande);
        $entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }


    #[Route('/admin/commande/status/{id}', name: 'admin_commande_status', methods: ['POST'])]
    public function modifierStatutCommande(Request $request, Commande $commande, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['status'])) {
            return new JsonResponse(['success' => false, 'message' => 'Statut manquant.'], 400);
        }
    
        $nouveauStatus = $data['status'];
        $statutsValides = ['En cours', 'Expédiée', 'Livrée'];
    
        if (!in_array($nouveauStatus, $statutsValides)) {
            return new JsonResponse(['success' => false, 'message' => 'Statut invalide.'], 400);
        }
    
        $commande->setStatus($nouveauStatus);
        $entityManager->flush();
    
        return new JsonResponse(['success' => true, 'message' => 'Statut mis à jour.']);
    }
    
    
    
    #[Route('/mes-commandes', name: 'mes_commandes')]
    public function mesCommandes(): Response
    {
        $user = $this->getUser();
        $commandes = $user ? $user->getCommandes() : [];
    
        return $this->render('commande/user_commandes.html.twig', [
            'commandes' => $commandes
        ]);
    }
    




    #[Route('/admin/commandes/details/{id}', name: 'admin_commande_details', methods: ['GET'])]
    public function getCommandeDetails(Commande $commande): JsonResponse
    {
        $details = [];

        foreach ($commande->getLigneCommandes() as $ligne) {
            $produit = $ligne->getProduit();
            
            if ($produit === null) {
                continue;
            }

            $details[] = [
                'produit' => $produit->getLibelle(), 
                'reference' => method_exists($produit, 'getReference') ? $produit->getReference() : 'N/A',
                'quantite' => $ligne->getNbrProduits(),
                'prix' => number_format($produit->getPrix(), 2, ',', ' ') . " €",
            ];
        }

        return new JsonResponse(['details' => $details], Response::HTTP_OK);
    }

    #[Route('/commande/valider', name: 'commande_valider')]
    public function validerCommande(
        Request $request,
        Security $security,
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_afficher');
        }

        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour passer une commande.');
            return $this->redirectToRoute('app_login');
        }

        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDateCreation(new \DateTime());

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $commande->addProduit($produit, $quantite); 
            }
        }

        $entityManager->persist($commande);
        $entityManager->flush();

        $session->remove('panier');

        $this->addFlash('success', 'Votre commande a été validée avec succès !');
        return $this->redirectToRoute('app_boutiqueuser');
    }


    #[Route('/admin/clear-stock-alert', name: 'clear_stock_alert', methods: ['POST'])]
    public function clearStockAlert(SessionInterface $session): JsonResponse
    {
        $session->remove('produits_rupture');
        return new JsonResponse(['success' => true]);
    }
    
    
    

    #[Route('/admin/commandes/check-new', name: 'admin_check_new_commandes', methods: ['GET'])]
    public function checkNewOrders(SessionInterface $session, CommandeRepository $commandeRepository): JsonResponse 
    {
        // Récupérer l'ID de la dernière commande stockée en session
        $lastOrderId = $session->get('last_order_id', 0);
    
        // Récupérer la dernière commande en base de données
        $latestCommande = $commandeRepository->findOneBy([], ['id' => 'DESC']);
    
        // Vérifier si une nouvelle commande est disponible
        if ($latestCommande && $latestCommande->getId() > $lastOrderId) {
            // Mettre à jour l'ID de la dernière commande en session
            $session->set('last_order_id', $latestCommande->getId());
    
            // Retourner les détails de la nouvelle commande
            return new JsonResponse([
                'new' => true,
                'commande_id' => $latestCommande->getId(),
                'client' => $latestCommande->getNom() . ' ' . $latestCommande->getPrenom(),
                'total' => number_format($latestCommande->getPrixtotale(), 2, ',', ' ') . '€',
            ]);
        }
    
        // Aucune nouvelle commande trouvée
        return new JsonResponse(['new' => false]);
    }
    

    
}
