<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Enum\EtatReclamtion;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


final class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
    public function showAllReclamation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etatFilter = $request->query->get('etat', 'tout');
        
        $reclamations = $this->filtrage($etatFilter, $entityManager);

        return $this->render('reclamation/showReclamation.html.twig', [
            'reclamations' => $reclamations,
            'etatFilter' => $etatFilter,
        ]);
    }
    
    #[Route('/reclamation/{id}/repondre', name: 'app_reclamation_repondre')]
    public function repondre(Reclamation $reclamation, Request $request, EntityManagerInterface $em): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setReclamation($reclamation);
            $reclamation->setEtat(EtatReclamtion::TRAITE);
            $reponse->setDate(new \DateTime());
            $em->persist($reponse);
            $em->flush();
        
            $this->addFlash('success', 'Réponse enregistrée avec succès.');
            return $this->redirectToRoute('app_reclamation');
        }
        

        return $this->render('reclamation/repondreReclamation.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }

    
    private function filtrage(string $etatFilter, EntityManagerInterface $entityManager)
    {
        $queryBuilder = $entityManager->getRepository(Reclamation::class)->createQueryBuilder('r')
            ->orderBy('r.dateEnvoie', 'DESC'); 

        if ($etatFilter !== 'tout') {
            $queryBuilder->andWhere('r.etat = :etat')
                         ->setParameter('etat', $etatFilter);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    #[Route('/reclamation/mes-reclamations', name: 'app_reclamation_mes')] // Combined route
public function showMyReclamations(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $entityManager->getRepository(User::class)->find(2); // Get the user (replace 2 with actual user logic)

    // 1. Handle Reclamation Form (Add Reclamation)
    $reclamation = new Reclamation();
    $reclamation->setUser($user);
    $reclamation->setEtat(EtatReclamtion::ATTENTE);
    $reclamation->setDateEnvoie(new \DateTime());

    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reclamation);
        $entityManager->flush();
        $this->addFlash('success', 'Votre réclamation a été soumise avec succès !');
        return $this->redirectToRoute('app_reclamation_mes'); // Redirect to the same page
    }


    // 2. Fetch Reclamations (Show My Reclamations)
    $etatFilter = $request->query->get('etat', 'tout');
    $criteria = ['user' => $user];

    if ($etatFilter !== 'tout') {
        $criteria['etat'] = $etatFilter;
    }

    $reclamations = $entityManager->getRepository(Reclamation::class)->findBy($criteria, ['dateEnvoie' => 'DESC']);


    return $this->render('reclamation/showMesReclamation.html.twig', [ // Combined template
        'reclamations' => $reclamations,
        'etatFilter' => $etatFilter,
        'form' => $form->createView(), // Pass the form to the template
    ]);
}

    #[Route('/reclamation/delete/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function deleteReclamation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->request->get('id');
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

        if (!$reclamation) {
            $this->addFlash('error', 'Réclamation non trouvée.');
            return $this->redirectToRoute('app_reclamation');
        }

        $entityManager->remove($reclamation);
        $entityManager->flush();

        $this->addFlash('success', 'Réclamation supprimée avec succès.');
        return $this->redirectToRoute('app_reclamation_mes');
    }

    #[Route('/reclamation/details', name: 'app_reclamation_detail', methods: ['POST'])]
    public function traiterReclamation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->request->get('id');
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

        if (!$reclamation) {
            return $this->json(['success' => false, 'message' => 'Réclamation non trouvée.']);
        }

        $user = $reclamation->getUser();

        return $this->json([
            'success' => true,
            'user' => [
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'photoProfil' => $user -> getPhotoProfil(),

            ],
            'dateEnvoie' => $reclamation->getDateEnvoie()->format('Y-m-d'),
            'etat' => $reclamation->getEtat(),
            'description' => $reclamation->getDescription(),
            'id'=>$reclamation->getId(),
            'reponse'=> $reclamation->getReponse()->getReponse()
        ]);
    }

    #[Route('/reclamation/detail', name: 'app_mes_reclamation_detail', methods: ['POST'])]
    public function voirReclamation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->request->get('id');
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

        if (!$reclamation) {
            return $this->json(['success' => false, 'message' => 'Réclamation non trouvée.']);
        }

        $user = $reclamation->getUser();

        return $this->json([
            'success' => true,
            'user' => [
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'photoProfil' => $user -> getPhotoProfil(),

            ],
            'dateEnvoie' => $reclamation->getDateEnvoie()->format('Y-m-d'),
            'etat' => $reclamation->getEtat(),
            'description' => $reclamation->getDescription(),
            'reponse'=> $reclamation->getReponse()->getReponse()

        ]);
    }
}