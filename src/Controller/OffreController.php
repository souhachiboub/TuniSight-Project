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


#[Route('/prestataire')]
class OffreController extends AbstractController
{
    #[Route('/offre/new', name: 'offre_create', methods: ['GET', 'POST'])]
public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
{
    $offre = new Offre();
    $form = $this->createForm(OffreType::class, $offre);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }

            return $this->redirectToRoute('offre_show');
        } else {
            // Si le formulaire n'est pas valide, on renvoie les erreurs
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'errors' => $errors], 400);
            }
        }
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

    
    



#[Route('/offres', name: 'offre_show', methods: ['GET', 'POST'])]
public function index(Request $request, OffreRepository $offreRepository, PaginatorInterface $paginator): Response
{
    $expired = $request->query->get('expired');

    // Créer un QueryBuilder pour récupérer les offres
    $queryBuilder = $offreRepository->createQueryBuilder('o');

    if ($expired !== null && $expired !== '') {
        $expired = (int) $expired;
        $queryBuilder
            ->where('o.dateExpiration < :now') // Exemple de condition pour les offres expirées
            ->setParameter('now', new \DateTime())
            ->setMaxResults(5); // Optionnel, limite les résultats
    }

    $query = $queryBuilder->getQuery(); // Récupérer la requête QueryBuilder

    // Pagination
    $pagination = $paginator->paginate(
        $query, 
        $request->query->getInt('page', 1), 
        5 // Nombre d'éléments par page
    );

    return $this->render('offre/_offres.html.twig', [
        'offres' => $pagination
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
public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
{
    $form = $this->createForm(OffreType::class, $offre, ['is_edit' => true]);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }

            return $this->redirectToRoute('offre_show');
        } else {
            // Si le formulaire n'est pas valide, on renvoie les erreurs
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'errors' => $errors], 400);
            }
        }
    }

    if ($request->isXmlHttpRequest()) {
        return $this->render('offre/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    return $this->render('offre/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}
    
}
