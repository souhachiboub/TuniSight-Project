<?php

namespace App\Controller;

<<<<<<< HEAD
use App\Entity\User;
use App\Form\UserType;
use App\Form\AddAdminType;
use App\Form\InfoFormType;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
=======
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
>>>>>>> gestion-activites

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
<<<<<<< HEAD
    public function index(SessionInterface $session): Response
    {
        // Retrieve the user role from the session
        $role = $session->get('user_role');

        // Check if the role is not admin, artisant, or prestataire
        if (!in_array($role, ['admin', 'artisant', 'prestataire'])) {
            return $this->redirectToRoute('app_login'); // Redirect to login page
        }
=======
    public function index(): Response
    {
>>>>>>> gestion-activites
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
<<<<<<< HEAD


    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        // Clear all session data
        $session->clear();

        // Invalidate the session (optional, but recommended)
        $session->invalidate();
     
        // Redirect to login page
        return $this->redirectToRoute('app_login');   
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
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
                    $session->set('user_role', $existingUser->getRole());
                    // Redirect to 'user.id/mesinfo' if credentials are correct
                    return $this->redirectToRoute('mesinfo', ['id' => $existingUser->getId()], Response::HTTP_SEE_OTHER);
                
                }else {
                    $this->addFlash('error', 'Invalid email or password.');
                }
            } else {
                $this->addFlash('error', 'Invalid email or password.');
            }
        }

        return $this->render('admin/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handling password confirmation
            $motdepasse = $form->get('motdepasse')->getData();
        $confirmpwd = $form->get('confirmpwd')->getData();

        $user->setconfirmpwd($confirmpwd);

        if ($motdepasse !== $confirmpwd) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->render('admin/inscription.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        }
                // Continue with your user creation and password encoding logic

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('admin/inscription.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
    }

    

    
    #[Route('/addadmin', name: 'addadmin', methods: ['GET', 'POST'])]
    public function addadmin(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Retrieve the user role from the session
        $role = $session->get('user_role');

        // Check if the role is not admin, artisant, or prestataire
        if (!in_array($role, ['admin'])) {
            return $this->redirectToRoute('app_login'); // Redirect to login page
        }
        $user = new User();
        $form = $this->createForm(AddAdminType::class, $user);
        $form->handleRequest($request);
        $user->setRole("admin");
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('listUtilisateur', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/addadmin.html.twig', [
            'role' => $session->get('user_role'),
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
            'userId' => $session->get('user_id'),
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/listUtilisateur', name: 'listUtilisateur', methods: ['GET'])]
    public function listUtilisateur(UserRepository $userRepository, SessionInterface $session): Response
    {
        // Retrieve the user role from the session
        $role = $session->get('user_role');

        // Check if the role is not admin, artisant, or prestataire
        if (!in_array($role, ['admin'])) {
            return $this->redirectToRoute('app_login'); // Redirect to login page
        }
        return $this->render('admin/listUtilisateur.html.twig', [
            'role' => $session->get('user_role'),
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
            'userId' => $session->get('user_id'),
            'users' => $userRepository->findAll(),
        ]);
    }

    
    #[Route('/{id}/mesinfo', name: 'mesinfo', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Retrieve the user role from the session
        $role = $session->get('user_role');

        // Check if the role is not admin, artisant, or prestataire
        if (!in_array($role, ['admin', 'artisant', 'prestataire'])) {
            return $this->redirectToRoute('app_login'); // Redirect to login page
        }
        $form = $this->createForm(InfoFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('mesinfo', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/mesinfo.html.twig', [
            'role' => $session->get('user_role'),
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
            'userId' => $session->get('user_id'),
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/mdpoublier', name: 'mdpoublier', methods: ['GET'])]
    public function mdpoublier(UserRepository $userRepository): Response
    {
        return $this->render('admin/listUtilisateur.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    

=======
>>>>>>> gestion-activites
}
