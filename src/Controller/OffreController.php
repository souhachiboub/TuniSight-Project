<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Form\OffreType;
use App\Entity\Activite;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OffreController extends AbstractController
{
    
    #[Route('/offre/new', name: 'offre_create', methods: ['GET', 'POST'])]
public function create(Request $request, EntityManagerInterface $entityManager): Response
{
    $offre = new Offre();
    $form = $this->createForm(OffreType::class, $offre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($offre);
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        return $this->redirectToRoute('offre_show');
    }

    if ($request->isXmlHttpRequest()) {
        return $this->render('offre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    return $this->render('offre/new.html.twig', [
        'form' => $form->createView(),
    ]);
}



#[Route('/offres', name: 'offre_show')]
// public function show(OffreRepository $offreRepository, Request $request, PaginatorInterface $paginator): Response
// {
//     // Création de la requête Doctrine Query
//     $query = $offreRepository->createQueryBuilder('o')->getQuery();

//     // Appliquer la pagination
//     $pagination = $paginator->paginate(
//         $query, // Requête Doctrine
//         $request->query->getInt('page', 1), // Numéro de page (1 par défaut)
//         5 // Nombre d'offres par page
//     );

//     return $this->render('offre/show.html.twig', [
//         'offres' => $pagination, // On passe la pagination à Twig
//     ]);
// }

public function show(OffreRepository $offreRepository, Request $request, PaginatorInterface $paginator): Response
{
    // Récupérer l'état d'expiration de la requête
    $expirée = $request->query->get('expirée');

    // Appliquer le filtre sur l'état d'expiration
    $qb = $offreRepository->findByExpirationStatusQuery($expirée);

    // Pagination
    $query = $qb->getQuery();
    $pagination = $paginator->paginate(
        $query, 
        $request->query->getInt('page', 1), 
        5 
    );

    return $this->render('offre/show.html.twig', [
        'offres' => $pagination, 
    ]);
}

    #[Route('/{id}/delete', name: 'offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
    if ($this->isCsrfTokenValid('delete' . $offre->getId(), $request->request->get('_token'))) {
        $entityManager->remove($offre);
        $entityManager->flush();
        $this->addFlash('success', 'L\'offre a été supprimée avec succès.');
        return $this->redirectToRoute('offre_show');
    }
    $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');
    return $this->redirectToRoute('offre_show');
    }
    
    #[Route('/offre/{id}/edit', name: 'offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager,ValidatorInterface $validator): Response
    {
        $form = $this->createForm(OffreType::class, $offre,['is_edit' => true]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
    
            return $this->redirectToRoute('offre_show');
        }
    
        if ($request->isXmlHttpRequest()) {
            return $this->render('offre/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        $errors = $validator->validate($offre);

        // Si il y a des erreurs
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
    
            return new JsonResponse(['errors' => $errorMessages], 400);  // Renvoi des erreurs
        }
    
    
        return $this->render('offre/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
