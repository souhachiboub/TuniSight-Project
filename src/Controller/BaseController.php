<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\InscriptionuserType;
use App\Form\InfoFormType;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
<<<<<<< Updated upstream
<<<<<<< HEAD
=======
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
>>>>>>> Stashed changes
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


        #[Route('/logoutf', name: 'app_logout_front')]
        public function logout(SessionInterface $session): Response
        {
            // Clear all session data
            $session->clear();

            // Invalidate the session (optional, but recommended)
            $session->invalidate();
        
            // Redirect to login page
            return $this->redirectToRoute('app_base');   
        }
<<<<<<< Updated upstream
=======

class BaseController extends AbstractController
{
    #[Route('/', name: 'app_base')]
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
>>>>>>> gestion-activites
=======

        #[Route('/loginu', name: 'app_login_front', methods: ['GET', 'POST'])]
        public function login( Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
        {
            $user = new User();
            $form = $this->createForm(LoginType::class, $user);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $email = $form->get('email')->getData();
                $password = $form->get('motdepasse')->getData();
    
                $userRepository = $entityManager->getRepository(User::class);
                $existingUser = $userRepository->findOneBy(['email' => $email]);
    
                if ($existingUser) {
                    if($password== $existingUser->getMotdepasse())
                    {
                        // Store the user ID in the session
                        $session->set('user_id', $existingUser->getId());
                        $session->set('user_nom', $existingUser->getNom());
                        $session->set('user_prenom', $existingUser->getPrenom());
                        // Redirect to 'user.id/mesinfo' if credentials are correct
                        return $this->redirectToRoute('app_base');
                    
                    }else {
                        $this->addFlash('error', 'Invalid email or password.');
                    }
                } else {
                    $this->addFlash('error', 'Invalid email or password.');
                }
            }
    
            return $this->render('base/loginfront.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        #[Route('/inscriptionu', name: 'app_inscription_front', methods: ['GET', 'POST'])]
        public function inscriptionn(Request $request, EntityManagerInterface $entityManager): Response
        {
            $user = new User();
            $user->setRole("user");
            $form = $this->createForm(InscriptionuserType::class, $user);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                // Handling password confirmation
                $motdepasse = $form->get('motdepasse')->getData();
            $confirmpwd = $form->get('confirmpwd')->getData();
    
            $user->setconfirmpwd($confirmpwd);
    
            if ($motdepasse !== $confirmpwd) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('base/inscription.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }
    
    
                    // Continue with your user creation and password encoding logic
    
                    $entityManager->persist($user);
                    $entityManager->flush();
    
                    return $this->redirectToRoute('app_login_front', [], Response::HTTP_SEE_OTHER);
                }
    
                return $this->render('base/inscription.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
        }

    #[Route('/{id}/moncompte', name: 'moncompte', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $form = $this->createForm(InfoFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_base', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base/moncompte.html.twig', [
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
            'userId' => $session->get('user_id'),
            'user' => $user,
            'form' => $form,
        ]);
    }

>>>>>>> Stashed changes
}
