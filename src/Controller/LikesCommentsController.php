<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\LikesCommentaire;
use App\Entity\Commentaire;
use App\Repository\LikesCommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;

final class LikesCommentsController extends AbstractController
{
    #[Route('/likeCommentaire/{commentaireId}', name: 'toggle_like_comment', methods: ['POST'])]
    public function toggleLike(int $commentaireId, EntityManagerInterface $entityManager, LikesCommentaireRepository $likesRepository): JsonResponse
    {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($commentaireId);
        if (!$commentaire) {
            return new JsonResponse(['error' => 'commentaire$commentaire not found'], 404);
        }

        $user = $entityManager->getRepository(User::class)->find(1); // Test
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Vérifier si l'utilisateur a déjà liké la commentaire$commentaire
        $existingLike = $likesRepository->findOneBy(['commentaire' => $commentaire, 'user' => $user]);

        if ($existingLike) {
            $entityManager->remove($existingLike);
            $entityManager->flush();
            $liked = false;
        } else {
            $like = new LikesCommentaire();
            $like->setCommentaire($commentaire);
            $like->setUser($user);
            $entityManager->persist($like);
            $entityManager->flush();
            $liked = true;
        }

        // Calculer le nombre de likes
        $nbrLikes = $likesRepository->count(['commentaire' => $commentaire]);

        return new JsonResponse([
            'liked' => $liked,
            'likes' => $nbrLikes
        ]);
    }
}
