<?php

namespace App\Controller;

use App\Repository\UserEntityRepository;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\UserEntity;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;


class AdminController extends AbstractController
{
    #[Route('/users', name: 'app_list_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function showListUser(UserEntityRepository $userRepository, Request $request): Response
    {
        // Get the search query from the request
        $searchQuery = $request->query->get('searchQuery');
        
        // Get the selected role from the query string
        $roleFilter = $request->query->get('role');
        
        // Search users by name or surname if a search query is provided
        $users = $userRepository->searchByNameOrSurname($searchQuery);

        // If a role filter is selected, further filter the users by role
        if ($roleFilter) {
            $users = array_filter($users, function($user) use ($roleFilter) {
                return $user->getRole() === $roleFilter;
            });
        }

        // Render the list of users in the template
        return $this->render('admin/list.html.twig', [
            'users' => $users,
            'searchQuery' => $searchQuery,  // Pass the search query to the template for the search box
        ]);
    }

    #[Route('/user/{id}', name: 'user_details')]
    public function showUserDetails(UserEntityRepository $userRepository, int $id): Response
    {
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
    #[Route('/user/ban/{id}', name: 'admin_user_ban')]
    public function banUser(UserEntity $user, EntityManagerInterface $entityManager): Response
    {
        $user->setBanned(true);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('app_list_user');
    }
    #[Route('/user/unban/{id}', name: 'admin_user_unban')]
    public function unbanUser(UserEntity $user, EntityManagerInterface $entityManager): Response
    {
        $user->setBanned(false);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('app_list_user');
    }
    #[Route('/delete/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(UserEntity $user, EntityManagerInterface $em)
    {
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_list_user');
    }



}
