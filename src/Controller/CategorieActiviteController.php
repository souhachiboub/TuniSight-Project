<?php

namespace App\Controller;

use App\Entity\CategorieActivite;
use App\Form\CategorieActiviteType;
use App\Repository\CategorieActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categorie/activite')]
final class CategorieActiviteController extends AbstractController
{
    #[Route('/', name: 'app_categorie_activite_index', methods: ['GET'])]
    public function index(CategorieActiviteRepository $categorieActiviteRepository): Response
    {
        return $this->render('categorie_activite/index.html.twig', [
            'categorie_activites' => $categorieActiviteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_categorie_activite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieActivite = new CategorieActivite();
        $form = $this->createForm(CategorieActiviteType::class, $categorieActivite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $form->getErrors(true, false);
            dump($errors);
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($categorieActivite);
                $entityManager->flush();
                $this->addFlash('success', 'Catégorie ajoutée avec succès!');
                return $this->redirectToRoute('app_categorie_activite_index');
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire.');
            }
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            dump($form->getErrors(true));
        }
        return $this->render('categorie_activite/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_categorie_activite_show', methods: ['GET'])]
    public function show(CategorieActivite $categorieActivite): Response
    {
        return $this->render('categorie_activite/show.html.twig', [
            'categorie_activite' => $categorieActivite,
        ]);
    }

    #[Route('{id}/edit', name: 'app_categorie_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieActivite $categorieActivite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieActiviteType::class, $categorieActivite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_categorie_activite_index');
        }

        return $this->render('categorie_activite/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_activite_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieActivite $categorieActivite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categorieActivite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorieActivite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_activite_index', [], Response::HTTP_SEE_OTHER);
    }
}
