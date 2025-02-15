<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Publication;
use App\Repository\LikesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class LikesController extends AbstractController
{
    #[Route('/like/{publicationId}', name: 'toggle_like', methods: ['POST'])]
    public function toggleLike(int $publicationId, EntityManagerInterface $entityManager, LikesRepository $likesRepository): JsonResponse
    {
        $publication = $entityManager->getRepository(Publication::class)->find($publicationId);
        if (!$publication) {
            return new JsonResponse(['error' => 'Publication not found'], 404);
        }

        $user = $entityManager->getRepository(User::class)->find(1); // Test
        

        $existingLike = $likesRepository->findOneBy(['publication' => $publication, 'user' => $user]);

        if ($existingLike) {
            $entityManager->remove($existingLike);
            $entityManager->flush();
            $liked = false;
        } else {
            $like = new Likes();
            $like->setPublication($publication);
            $like->setUser($user);
            $entityManager->persist($like);
            $entityManager->flush();
            $liked = true;
        }

        $nbrLikes = $likesRepository->count(['publication' => $publication]);

        return new JsonResponse([
            'liked' => $liked,
            'likes' => $nbrLikes,
        ]);
    }
}
