<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils,SessionInterface $session,Request $request): Response
    {

        $redirectUrl = $request->get('redirect', null);

        if ($redirectUrl) {
            $session->set('redirect_url', $redirectUrl);
        }

        if ($this->getUser()) {
            $redirectUrl = $session->get('redirect_url', $this->generateUrl('app_index'));

            return $this->redirect($redirectUrl);
        }

        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();

        // Vérifier les rôles et rediriger en conséquence
        if (in_array('ROLE_PRESTATAIRE', $roles)) {
            return $this->redirectToRoute('voucher_show');
        }elseif (in_array('ROLE_ARTISAN', $roles)) {
            return $this->redirectToRoute('app_produit');
        }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
