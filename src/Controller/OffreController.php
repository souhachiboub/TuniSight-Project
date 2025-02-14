<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Form\OffreType;
use App\Entity\Activite;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
class OffreController extends AbstractController
{
    
    #[Route('/create', name: 'offer_create')]
public function create(Request $request, EntityManagerInterface $em): Response
{
    $offer = new Offre();
    $form = $this->createForm(OffreType::class, $offer);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer l'activité sélectionnée dans le formulaire
        $activity = $offer->getActivitie();

        // Appliquer la réduction au prix de l'activité si une réduction est spécifiée
        $discount = $offer->getReduction(); // Par exemple, un pourcentage de réduction

        if ($discount) {
            $prixInitial = $activity->getPrix(); // Le prix de l'activité
            $prixAvecReduction = $prixInitial - ($prixInitial * $discount / 100);
            $activity->setPrix($prixAvecReduction); // Mettre à jour le prix de l'activité avec la réduction
        }

        // Persist l'offre dans la base de données
        $em->persist($offer);
        $em->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'L\'offre a été ajoutée avec succès!');

        return $this->redirectToRoute('offre_show');
    }

    return $this->render('offre/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/offres', name: 'offre_show')]
    public function index(OffreRepository $offreRepository): Response
    {
    return $this->render('offre/show.html.twig', [
        'offres' => $offreRepository->findAll(),
    ]);
    }
    #[Route('/{id}/delete', name: 'offre_delete')]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
    if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
        $entityManager->remove($offre);
        $entityManager->flush();
        $this->addFlash('success', 'L\'offre a été supprimée avec succès.');
    } else {
        $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');
    }

    return $this->redirectToRoute('offre_show');
    }

    #[Route('/offre/{id}/edit', name: 'offre_edit')]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
    $form = $this->createForm(OffreType::class, $offre, [
        'is_edit' => true
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'L\'offre a été mise à jour avec succès!');
        return $this->redirectToRoute('offre_show');
    }

    return $this->render('offre/edit.html.twig', [
        'offre' => $offre,
        'form' => $form->createView(),
    ]);
}
}
