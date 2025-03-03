<?php

namespace App\Controller;
use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Form\CommandeType;
use App\Repository\ProduitRepository;
use App\Service\StripeService;

final class PaiemmentcontrollerController extends AbstractController
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * ✅ Route pour afficher la page panier avec le formulaire
     */
  
    
    
    

    /**
     * ✅ Route pour enregistrer les données du formulaire en session AVANT le paiement
     */
    #[Route('/save-commande', name: 'save_commande', methods: ['POST'])]
    public function saveCommande(SessionInterface $session, Request $request, FormFactoryInterface $formFactory): JsonResponse
    {
        try {
            $commande = new Commande();
            $form = $formFactory->create(CommandeType::class, $commande);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                // ✅ Stocker les données en session
                $session->set('commande_data', [
                    'nom' => $commande->getNom(),
                    'prenom' => $commande->getPrenom(),
                    'adresseliv' => $commande->getAdresseliv(),
                    'numerotelephone' => $commande->getNumerotelephone(),
                    'email' => $commande->getEmail(),
                ]);
    
                return new JsonResponse(['success' => true]);
            }
    
            // 🛑 Renvoyer les erreurs sous chaque champ
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }
    
            return new JsonResponse([
                'success' => false,
                'errors' => $errors, // ✅ On envoie un JSON structuré
            ], 400);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
     
    
    
    
    

    /**
     * ✅ Route pour initier le paiement Stripe
     */
    #[Route('/checkoutraw', name: 'checkoutraw', methods: ['POST'])]
    public function checkout(SessionInterface $session, ProduitRepository $produitRepository): JsonResponse
    {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            return new JsonResponse(['error' => 'Panier vide'], 400);
        }

        $lineItems = [];
        foreach ($panier as $produitId => $quantite) {
            $produit = $produitRepository->find($produitId);
            if (!$produit) {
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $produit->getLibelle(),
                    ],
                    'unit_amount' => $produit->getPrix() * 100,
                ],
                'quantity' => $quantite,
            ];
        }

        // Création de la session Stripe
        $sessionStripe = $this->stripeService->createCheckoutSession(
            $lineItems,
            $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        if (!$sessionStripe || empty($sessionStripe->id)) {
            return new JsonResponse(['error' => 'Échec de la création de la session Stripe'], 500);
        }

        return new JsonResponse(['id' => $sessionStripe->id]);
    }

    /**
     * ✅ Route pour confirmer le paiement et enregistrer la commande
     */



     #[Route('/payment/success', name: 'payment_success')]
     public function paymentSuccess(
         SessionInterface $session,
         EntityManagerInterface $em,
         Security $security,
         ProduitRepository $produitRepository
     ): Response {
         $panier = $session->get('panier', []);
         $commandeData = $session->get('commande_data');
     
         if (empty($panier) || empty($commandeData)) {
             return $this->render('payment/error.html.twig', [
                 'message' => 'Aucune commande trouvée.',
             ]);
         }
     
         $user = $security->getUser();
         if (!$user) {
             return $this->render('payment/error.html.twig', [
                 'message' => 'Utilisateur non authentifié.',
             ]);
         }
     
         // ✅ Création de la commande
         $commande = new Commande();
         $commande->setUser($user);
         $commande->setNom($commandeData['nom']);
         $commande->setPrenom($commandeData['prenom']);
         $commande->setAdresseliv($commandeData['adresseliv']);
         $commande->setNumerotelephone($commandeData['numerotelephone']);
         $commande->setEmail($commandeData['email']);
         $commande->setDateCreation(new \DateTime());
     
         $nbrProdTotal = 0;
         $prixTotal = 0;
         $produitsEnRupture = [];
     
         foreach ($panier as $produitId => $quantite) {
             $produit = $produitRepository->find($produitId);
             if ($produit) {
                 $nbrProdTotal += $quantite;
                 $prixTotal += $produit->getPrix() * $quantite;
     
                 // ✅ Mise à jour du stock
                 if ($produit->getQuantite() >= $quantite) {
                     $produit->setQuantite($produit->getQuantite() - $quantite);
                     if ($produit->getQuantite() == 0) {
                         $produitsEnRupture[] = $produit->getLibelle();
                     }
                 }
             }
         }
     
         $commande->setNbrProdTotal($nbrProdTotal);
         $commande->setPrixtotale($prixTotal);
         $em->persist($commande);
         $em->flush();
     
         foreach ($panier as $produitId => $quantite) {
             $produit = $produitRepository->find($produitId);
             if ($produit) {
                 $ligneCommande = new LigneCommande();
                 $ligneCommande->setCommande($commande);
                 $ligneCommande->setProduit($produit);
                 $ligneCommande->setNbrProduits($quantite);
                 $em->persist($ligneCommande);
             }
         }
         $em->flush();
     
         // ✅ Stocker les produits en rupture pour l'admin
         if (!empty($produitsEnRupture)) {
             $session->set('produits_rupture', $produitsEnRupture);
         }
     
         // ✅ Maintenant on peut vider le panier
         $session->remove('panier');
         $session->remove('commande_data');
     
         return $this->render('/paiemmentcontroller/success.html.twig', [
             'message' => 'Paiement réussi, commande validée !',
             'commande' => $commande
         ]);
     }
     
     
     



    
    
     
    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'message' => 'Le paiement a été annulé. Vous pouvez réessayer.',
        ]);
    }









    #[Route('/verifier-stock', name: 'verifier_stock', methods: ['POST'])]
    public function verifierStock(SessionInterface $session, ProduitRepository $produitRepository): JsonResponse
    {
        $panier = $session->get('panier', []);
        $problemesStock = [];
    
        foreach ($panier as $produitId => $quantite) {
            $produit = $produitRepository->find($produitId);
            if ($produit && $produit->getQuantite() < $quantite) {
                $problemesStock[] = [
                    'produit' => $produit->getLibelle(),
                    'quantite_disponible' => $produit->getQuantite(),
                    'quantite_demandee' => $quantite,
                ];
            }
        }
    
        if (!empty($problemesStock)) {
            return new JsonResponse(['success' => false, 'erreurs' => $problemesStock], 400);
        }
    
        return new JsonResponse(['success' => true]);
    }
    











}


