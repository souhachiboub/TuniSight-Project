<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\Image;
use App\Entity\Likes;
use App\Entity\User;
use App\Entity\Commentaire;
use App\Entity\LikesCommentaire;
use App\Form\CommentaireType;
use App\Form\PublicationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\LikesRepository;

class PublicationController extends AbstractController
{
    #[Route('/publications', name: 'list_publications')]
    public function listPublications(Request $request, EntityManagerInterface $entityManager, LikesRepository $likesRepository): Response
    {
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // Formulaire invalide, renvoyer les erreurs
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
    
                return $this->json([
                    'success' => false,
                    'errors' => $errors,
                ]);
            } else {
                $publication->setDatePublication(new \DateTime());
                $user = $entityManager->getRepository(User::class)->find(1); // Assuming user with ID 1
                $publication->setUser($user);
    
                $entityManager->persist($publication);
                $entityManager->flush();
    
                // --- Gestion des images ---
                $imageFiles = $form->get('images')->getData();
                if ($imageFiles) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/publication';
                    foreach ($imageFiles as $imageFile) {
                        if ($imageFile) {
                            // Validate file type (e.g., only images)
                            if (!in_array($imageFile->guessExtension(), ['jpg', 'png', 'jpeg', 'gif'])) {
                                throw new \Exception('Seuls les fichiers images sont autorisés.');
                            }
    
                            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                            try {
                                $imageFile->move($uploadDir, $newFilename);
                            } catch (FileException $e) {
                                throw new \Exception('Erreur lors du téléchargement de l\'image.');
                            }
    
                            $image = new Image();
                            $image->setUrl('/publication/' . $newFilename); // Chemin relatif
                            $image->setPublication($publication);
                            $entityManager->persist($image);
                        }
                    }
                    $entityManager->flush();
                }
    
                $this->addFlash('success', 'Publication ajoutée avec succès !');
                return $this->redirectToRoute('list_publications');
            }
        }
    
        $publications = $entityManager->getRepository(Publication::class)->findBy([], ['datePublication' => 'DESC']);
        $user = $entityManager->getRepository(User::class)->find(1); // Assuming user with ID 1
    
        foreach ($publications as $pub) {
            // Check if the user has liked this publication
            $isLiked = $entityManager->getRepository(Likes::class)->findOneBy([
                'user' => $user,
                'publication' => $pub
            ]);
            $pub->isLiked = $isLiked !== null;
        }
    
        return $this->render('publication/test.html.twig', [
            'publications' => $publications,
            'form' => $form->createView(),
        ]);
    }
    
#[Route('/publications/details/{id}', name: 'detail_publications')]
public function DetailsPublications($id, Request $request, EntityManagerInterface $entityManager, LikesRepository $likesRepository): Response
{
    $publication = $entityManager->getRepository(Publication::class)->find($id);
    if (!$publication) {
        throw $this->createNotFoundException('La publication n\'existe pas.');
    }

    $user = $entityManager->getRepository(User::class)->find(1); // Assume user ID is 1
    $isLiked = $entityManager->getRepository(Likes::class)->findOneBy([
        'user' => $user,
        'publication' => $publication
    ]);
    $publication->isLiked = $isLiked !== null;

    $commentaires = $entityManager->getRepository(Commentaire::class)->findBy(['publication' => $publication], ['date' => 'DESC']);

    foreach ($commentaires as $commentaire) {
        $isLikedComment = $entityManager->getRepository(LikesCommentaire::class)->findOneBy([
            'user' => $user,
            'commentaire' => $commentaire
        ]);
        $commentaire->isLiked = $isLikedComment !== null;
    }

    // Créer un formulaire pour ajouter un commentaire
    $formComment = $this->createForm(CommentaireType::class);

    // Gérer la soumission du formulaire de commentaire
    $formComment->handleRequest($request);
    if ($formComment->isSubmitted() && $formComment->isValid()) {
        $commentaire = $formComment->getData();
        $commentaire->setPublication($publication);
        $commentaire->setUser($user);
        $commentaire->setDate(new \DateTime());

        $entityManager->persist($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire ajouté avec succès !');
        return $this->redirectToRoute('detail_publications', ['id' => $publication->getId()]);
    }

    return $this->render('publication/detailPublication.html.twig', [
        'publication' => $publication,
        'commentaires' => $commentaires,
        'formComment' => $formComment->createView(),
    ]);
}
#[Route('/publication/{publicationId}/images', name: 'get_images_for_publication')]
public function getImagesForPublication($publicationId, EntityManagerInterface $entityManager): Response
{
    $publication = $entityManager->getRepository(Publication::class)->find($publicationId);

    if (!$publication) {
        throw $this->createNotFoundException('La publication n\'existe pas.');
    }

    $images = $entityManager->getRepository(Image::class)->findBy(['publication' => $publication]);

    foreach ($images as $image) {
        // Use the absolute path or asset() if available
        $imageUrl = $this->getParameter('/publication/'). $image->getUrl(); // Assuming you have this parameter

        $imageData = [
            'url' => $imageUrl, // Or use asset() if you can
        ];
    }

    return $this->json($imageData);
}


}    
