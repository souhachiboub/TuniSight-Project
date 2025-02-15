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

    #[Route('/reclamation/mes-reclamations', name: 'app_reclamation_mes')]
    public function showMyReclamations(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find(2); 

        $etatFilter = $request->query->get('etat', 'tout'); 

        $criteria = ['user' => $user];

        if ($etatFilter !== 'tout') {
            $criteria['etat'] = $etatFilter;
        }

        $reclamations = $entityManager->getRepository(Reclamation::class)->findBy($criteria, ['dateEnvoie' => 'DESC']); // Tri par date

        return $this->render('reclamation/showMesReclamation.html.twig', [
            'reclamations' => $reclamations,
            'etatFilter' => $etatFilter, 
        ]);
    }

    #[Route('/reclamation/new', name: 'app_reclamation_new')]
    public function addReclamation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $user = $entityManager->getRepository(User::class)->find(2); // Test
        $reclamation->setUser($user);
        $reclamation->setEtat(EtatReclamtion::ATTENTE);

        $reclamation->setDateEnvoie(new \DateTime());
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Votre réclamation a été soumise avec succès !');
            return $this->redirectToRoute('app_reclamation_mes');
        }

        return $this->render('reclamation/addReclamation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reclamation/delete', name: 'app_reclamation_delete', methods: ['POST'])]
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
            'id'=>$reclamation->getId()
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
            'description' => $reclamation->getDescription()
        ]);
    }
}
