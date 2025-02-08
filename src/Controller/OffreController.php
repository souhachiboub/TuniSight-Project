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
        // Crée une nouvelle instance de l'entité Offer
        $offer = new Offre();

        // Création du formulaire pour l'offre
        $form = $this->createForm(OffreType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // L'offre est déjà liée à l'activité dans le formulaire via le champ "activity"
            // Si vous souhaitez effectuer des opérations supplémentaires sur l'activité, vous pouvez le faire ici

            // Sauvegarder l'offre dans la base de données
            $em->persist($offer);
            $em->flush();
            $this->addFlash('success', 'L\'offre a été ajoutée avec succès!');
            return $this->redirectToRoute('offre_show');
        }

        // Si le formulaire n'est pas encore soumis, afficher la vue
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
    // Vérifier si le token CSRF est valide pour éviter les suppressions non sécurisées
    if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
        $entityManager->remove($offre);
        $entityManager->flush();

        // Ajouter un message flash pour informer l'utilisateur
        $this->addFlash('success', 'L\'offre a été supprimée avec succès.');
    } else {
        $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');
    }

    return $this->redirectToRoute('offre_show');
    }

    #[Route('/offre/{id}/edit', name: 'offre_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
{
    // Passer l'option 'is_edit' à true pour désactiver le champ activitie
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
