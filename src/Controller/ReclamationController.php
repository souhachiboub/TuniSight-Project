<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\UserEntity;
use App\Form\ReclamationType;
use App\Enum\EtatReclamtion;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


final class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
public function showAllReclamation(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
{
    $etatFilter = $request->query->get('etat', 'tout');

    // Utilisation d'une requête paginable
    $queryBuilder = $entityManager->getRepository(Reclamation::class)->createQueryBuilder('r')
        ->orderBy('r.dateEnvoie', 'DESC');

    if ($etatFilter !== 'tout') {
        $queryBuilder->andWhere('r.etat = :etat')
                     ->setParameter('etat', $etatFilter);
    }

    $query = $queryBuilder->getQuery();

    // Pagination
    $reclamations = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1), // Numéro de la page actuelle
        10 // Nombre d’éléments par page
    );

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
        $user = $this->getUser();
    
        // 1. Handle Reclamation Form (Add Reclamation)
        $reclamation = new Reclamation();
        $reclamation->setUser($user);
        $reclamation->setEtat(EtatReclamtion::ATTENTE);
        $reclamation->setDateEnvoie(new \DateTime());
    
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($reclamation);
                $entityManager->flush();
    
                // Check if the request is an AJAX request
                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => true,
                        'message' => 'Votre réclamation a été soumise avec succès !',
                    ]);
                } else {
                    // For non-AJAX requests, add a flash message and redirect
                    $this->addFlash('success', 'Votre réclamation a été soumise avec succès !');
                    return $this->redirectToRoute('app_reclamation_mes');
                }
            } else {
                // Handle form errors
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
    
                // Return JSON response for AJAX requests
                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => false,
                        'errors' => $errors,
                    ]);
                } else {
                    // For non-AJAX requests, render the template with errors
                    $this->addFlash('error', 'Il y a des erreurs dans le formulaire.');
                }
            }
        }
    
        // 2. Fetch Reclamations (Show My Reclamations)
        $etatFilter = $request->query->get('etat', 'tout');
        $criteria = ['user' => $user];
    
        if ($etatFilter !== 'tout') {
            $criteria['etat'] = $etatFilter;
        }
    
        $reclamations = $entityManager->getRepository(Reclamation::class)->findBy($criteria, ['dateEnvoie' => 'DESC']);
    
        // Check if the request is an AJAX request (for filtering)
        if ($request->isXmlHttpRequest()) {
            // Serialize reclamations for JSON response
            $serializedReclamations = array_map(function ($reclamation) {
                return [
                    'id' => $reclamation->getId(),
                    'description' => $reclamation->getDescription(),
                    'etat' => $reclamation->getEtat(),
                    'dateEnvoie' => $reclamation->getDateEnvoie()->format('Y-m-d H:i:s'),
                    // Add other fields as needed
                ];
            }, $reclamations);
    
            return $this->json([
                'reclamations' => $serializedReclamations,
                'etatFilter' => $etatFilter,
            ]);
        } else {
            // For non-AJAX requests, render the template
            return $this->render('reclamation/showMesReclamation.html.twig', [
                'reclamations' => $reclamations,
                'etatFilter' => $etatFilter,
                'form' => $form->createView(),
            ]);
        }
    }
#[Route('/reclamation/delete/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
public function deleteReclamation($id, Request $request, EntityManagerInterface $entityManager): Response
{
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
            'reponse'=>$reclamation->getReponse()
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

    
   
#[Route('/reclamation/{id}/modifier', name: 'app_reclamation_modifier')]
public function modifierReclamation(Reclamation $reclamation, Request $request, EntityManagerInterface $em): Response
{
    if ($reclamation->getEtat() !== 'en attente') {
        $this->addFlash('error', 'Seules les réclamations en attente peuvent être modifiées.');
        return $this->redirectToRoute('app_reclamation_mes');
    }

    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Réclamation modifiée avec succès.');
        return $this->redirectToRoute('app_reclamation_mes');
    }

    return $this->render('reclamation/modifierReclamation.html.twig', [
        'form' => $form->createView(),
        'reclamation' => $reclamation,
    ]);
}

#[Route('/reclamation/{id}/voir', name: 'app_reclamation_voir')]
public function voirReclamationDetail(Reclamation $reclamation): Response
{
    return $this->render('reclamation/detailReclamation.html.twig', [
        'reclamation' => $reclamation
    ]);
}



}
