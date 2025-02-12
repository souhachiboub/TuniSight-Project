<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/activite')]
final class ActiviteController extends AbstractController
{
    #[Route('/list', name: 'app_index', methods: ['GET'])]
    public function index(ActiviteRepository $activiteRepository): Response
    {
        return $this->render('base/index.html.twig', [
            'activites' => $activiteRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
    public function newActivite(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($activite);
            $entityManager->flush();

            return $this->redirectToRoute('app_activite_index');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('activite/_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        return $this->render('activite/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_activite_show', methods: ['GET'])]
    public function show(Activite $activite): Response
    {
        return $this->render('activite/show.html.twig', [
            'activite' => $activite,
        ]);
    }

    #[Route('{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_activite_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $activite = $entityManager->getRepository(Activite::class)->find($id);

        if (!$activite) {
            throw new NotFoundHttpException('Activité non trouvée.');
        }

        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $id, $token)) {
            $imagePath = $activite->getImage();
            if ($imagePath && !empty($imagePath)) {
                $filesystem = new Filesystem();
                $imageFullPath = $this->getParameter('kernel.project_dir') . '/public/uploads/activites/' . $imagePath;
                if ($filesystem->exists($imageFullPath)) {
                    $filesystem->remove($imageFullPath);
                }
            }

            $entityManager->remove($activite);
            $entityManager->flush();
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide.');
        }
        return $this->redirectToRoute('app_activite_index');
    }
}
