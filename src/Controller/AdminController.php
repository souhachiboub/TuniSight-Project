<?php

namespace App\Controller;

use App\Repository\UserEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/users', name: 'app_list_user')]
    // In AdminController.php
public function showListUser(UserEntityRepository $userRepository, Request $request): Response
{
    $user = $this->getUser();

    // Vérifie si l'utilisateur est connecté
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Vérifie si l'utilisateur n'a pas le rôle ROLE_ADMIN
    if (!$this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('app_login'); // Ou une autre route, par exemple 'app_home'
    }
    // Get the selected role from the query string
    $roleFilter = $request->query->get('role');
    
    // Map the role query value to the correct role constant in your system
    $roleMapping = [
        'user' => 'ROLE_USER',
        'prestataire' => 'ROLE_PRESTATAIRE',
        'artisan' => 'ROLE_ARTISAN',
    ];

    if ($roleFilter && isset($roleMapping[$roleFilter])) {
        $roleFilter = $roleMapping[$roleFilter];
    }

    // Get the search query
    $searchQuery = $request->query->get('searchQuery');
    
    // Search users by name or surname if a search query is provided
    $users = $userRepository->searchByNameOrSurname($searchQuery);

    // Filter users by role if the role filter is applied
    if ($roleFilter) {
        $users = array_filter($users, function($user) use ($roleFilter) {
            return in_array($roleFilter, $user->getRoles());
        });
    }

    // Render the list of users
    return $this->render('admin/list.html.twig', [
        'users' => $users,
        'searchQuery' => $searchQuery,
    ]);
}


    #[Route('/user/{id}', name: 'user_details')]
    public function showUserDetails(UserEntityRepository $userRepository, int $id): Response
    {
        $user = $this->getUser();

    // Vérifie si l'utilisateur est connecté
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Vérifie si l'utilisateur n'a pas le rôle ROLE_ADMIN
    if (!$this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('app_login'); // Ou une autre route, par exemple 'app_home'
    }
        // Fetch user by ID
        $user = $userRepository->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Render the user details page
        return $this->render('admin/user_details.html.twig', [
            'user' => $user,
        ]);
    }
}
