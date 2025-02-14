<?php
namespace App\Controller;

use App\Entity\Pack;
use App\Form\PackType;
use App\Entity\Produit;
use App\Form\RechercheProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PackController extends AbstractController
{
    #[Route('/pack', name: 'app_pack')]
    public function createPackPromotionnel(Request $request, EntityManagerInterface $em): Response
    {
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);

        // Traitement de la soumission du formulaire pour créer un pack
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pack);
            $em->flush();

            // Redirection vers la page où on peut ajouter des produits au pack
            return $this->redirectToRoute('app_produit', ['id' => $pack->getId()]);
        }

        return $this->render('pack/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pack/{id}/prod', name: 'app_produit')]
    public function ajouterProduitPack(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $pack = $em->getRepository(Pack::class)->find($id);
        if (!$pack) {
            throw $this->createNotFoundException('Pack non trouvé');
        }
        $produitRechercheForm = $this->createForm(RechercheProduitType::class);
        $produitRechercheForm->handleRequest($request);
        if ($produitRechercheForm->isSubmitted() && $produitRechercheForm->isValid()) {
            $searchData = $produitRechercheForm->getData();
            $criteria = $searchData->getLibelle();
            $produits = $em->getRepository(Produit::class)->findByCriteria($criteria);
        } 
         else {
            $produits = [];
        }

        if ($request->isMethod('POST') && $request->get('produit_id')) {
            $produitId = $request->get('produit_id');
            $produit = $em->getRepository(Produit::class)->find($produitId);

            if ($produit) {
                $pack->addProduit($produit);
                $em->persist($pack);
                $em->flush();
                $this->addFlash('success', 'Produit ajouté au pack');
            }
        }

        return $this->render('pack/chercher.html.twig', [
            'pack' => $pack,
            'produitRechercheForm' => $produitRechercheForm->createView(),
            'produits' => $produits,
        ]);
    }
}
