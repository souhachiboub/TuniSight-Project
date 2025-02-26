<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;  // Ajout de la classe Request
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
//     #[Route(path: '/login', name: 'app_login')]
// public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
// {
//     // Get the login error if there is one
//     $error = $authenticationUtils->getLastAuthenticationError();
//     // Last email entered by the user
//     $lastEmail = $authenticationUtils->getLastUsername();

//     // Save the last email entered in the session
//     $request->getSession()->set('LAST_EMAIL', $lastEmail);  // Stocker l'email dans la session

//     // If the user is already logged in, redirect them based on their role
//     if ($this->getUser()) {
//         // Get the roles of the logged-in user
//         $roles = $this->getUser()->getRoles();

//         // Check the roles and redirect accordingly
//         if (in_array('ROLE_ADMIN', $roles)) {
//             return $this->redirectToRoute('admin_list');
//         } elseif (in_array('ROLE_PRESTATAIRE', $roles)) {
//             return $this->redirectToRoute('voucher_show');
//         } elseif (in_array('ROLE_ARTISAN', $roles)) {
//             return $this->redirectToRoute('artisan_dashboard');
//         } else {
//             return $this->redirectToRoute('user_dashboard');
//         }
//     }

//     // If the user is not logged in, show the login page
//     return $this->render('security/login.html.twig', [
//         'last_username' => $lastEmail,  // Use the email here
//         'error' => $error,
//     ]);
// }


//     #[Route(path: '/logout', name: 'app_logout')]
//     public function logout(): void
//     {
//         // This method can be blank - it will be intercepted by the logout key on your firewall.
//         throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
//     }


#[Route(path: '/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    if ($this->getUser()) {
        // Récupérer le(s) rôle(s) de l'utilisateur connecté
        $roles = $this->getUser()->getRoles();

        // Vérifier les rôles et rediriger en conséquence
        if (in_array('ROLE_PRESTATAIRE', $roles)) {
            return $this->redirectToRoute('voucher_show');
        } elseif (in_array('ROLE_ARTISAN', $roles)) {
            return $this->redirectToRoute('app_produit');
        }
        // else {
        //     return $this->redirectToRoute('voucher_show');
        // }
    }

    return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
}

#[Route(path: '/logout', name: 'app_logout')]
public function logout(): void
{
    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
}


}
