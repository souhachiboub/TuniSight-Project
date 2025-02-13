<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class BaseController extends AbstractController
{
        #[Route('/', name: 'app_base')]
        public function index(SessionInterface $session): Response
        {
            return $this->render('base/index.html.twig', [
            'role' => $session->get('user_role'),
                'nom' => $session->get('user_nom'),
                'prenom' => $session->get('user_prenom'),
                'userId' => $session->get('user_id'),
                'controller_name' => 'BaseController',
            ]);
        }

        #[Route('/logout', name: 'app_logout')]
        public function logout(SessionInterface $session): Response
        {
            // Clear all session data
            $session->clear();

            // Invalidate the session (optional, but recommended)
            $session->invalidate();
        
            // Redirect to login page
            return $this->redirectToRoute('app_base');   
        }
}
