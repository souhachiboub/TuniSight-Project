<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('admin/login.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/inscription.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    
    #[Route('/addadmin', name: 'addadmin', methods: ['GET', 'POST'])]
    public function addadmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/addadmin.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/listUtilisateur', name: 'listUtilisateur', methods: ['GET'])]
    public function listUtilisateur(UserRepository $userRepository): Response
    {
        return $this->render('admin/listUtilisateur.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    
    #[Route('/mesinfo', name: 'mesinfo', methods: ['GET'])]
    public function mesinfo(UserRepository $userRepository): Response
    {
        return $this->render('admin/listUtilisateur.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/mdpoublier', name: 'mdpoublier', methods: ['GET'])]
    public function mdpoublier(UserRepository $userRepository): Response
    {
        return $this->render('admin/listUtilisateur.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

}
