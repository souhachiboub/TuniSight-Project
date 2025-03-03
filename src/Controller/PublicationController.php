<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\Image;
use App\Entity\Likes;
use App\Entity\UserEntity;
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
use Knp\Component\Pager\PaginatorInterface;




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
    
                // Retourner les erreurs sous forme de JSON
                return $this->json([
                    'success' => false,
                    'errors' => $errors,
                ]);
            } else {
                $publication->setDatePublication(new \DateTime());
                $user = $this->getUser(); // Assuming user with ID 1
                $publication->setUser($user);
    
                $entityManager->persist($publication);
    
                // --- Gestion des images ---
                $imageFiles = $form->get('images')->getData();
                if ($imageFiles) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/publication';
                    foreach ($imageFiles as $imageFile) {
                        if ($imageFile) {
                            // Validation du type de fichier (images seulement)
                            if (!in_array($imageFile->guessExtension(), ['jpg', 'png', 'jpeg', 'gif'])) {
                                // Retourner une erreur si le fichier n'est pas une image
                                return $this->json([
                                    'success' => false,
                                    'errors' => ['Seuls les fichiers images sont autorisés.'],
                                ]);
                            }
    
                            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                            try {
                                $imageFile->move($uploadDir, $newFilename);
                            } catch (FileException $e) {
                                // Gérer l'exception pour éviter des erreurs serveur visibles
                                return $this->json([
                                    'success' => false,
                                    'errors' => ['Erreur lors du téléchargement de l\'image.'],
                                ]);
                            }
    
                            $image = new Image();
                            $image->setUrl('/publication/' . $newFilename); // Chemin relatif
                            $image->setPublication($publication);
                            $entityManager->persist($image);
                        }
                    }
                }
    
                // Persister les images et publier la publication
                $entityManager->flush();
    
                // Retourner un succès avec redirection pour recharger
                return $this->json([
                    'success' => true,
                    'redirectUrl' => $this->generateUrl('list_publications'), 
                ]);
            }
        }

 // Récupération de la chaîne de recherche (ex: /publications?q=texte)
 $searchQuery = $request->query->get('q', '');

 if (!empty($searchQuery)) {
     // Vous pouvez créer une méthode personnalisée dans le repository pour filtrer par recherche
     $publications = $entityManager->getRepository(Publication::class)->findBySearch($searchQuery);
 } else {
     $publications = $entityManager->getRepository(Publication::class)->findBy([], ['datePublication' => 'DESC']);
 }

 $user = $this->getUser();

 // Gestion des likes pour chaque publication
 foreach ($publications as $pub) {
     $isLiked = $entityManager->getRepository(Likes::class)->findOneBy([
         'user' => $user,
         'publication' => $pub
     ]);
     $pub->isLiked = $isLiked !== null;
 }

 return $this->render('publication/list.html.twig', [
     'publications' => $publications,
     'form' => $form->createView(),
     'searchQuery' => $searchQuery,
     'user' => $user,
 ]);
}
#[Route('/admin/publications', name: 'admin_list_publications')]
public function listPublicationsAdmin(
    Request $request,
    EntityManagerInterface $entityManager,
    PaginatorInterface $paginator
): Response {
    $searchQuery = $request->query->get('searchQuery', '');

    if (!empty($searchQuery)) {
        $query = $entityManager->getRepository(Publication::class)->findBySearchQuery($searchQuery);
    } else {
        $query = $entityManager->getRepository(Publication::class)->createQueryBuilder('p')
            ->orderBy('p.datePublication', 'DESC')
            ->getQuery();
    }

    // Pagination
    $publications = $paginator->paginate(
        $query, // La requête DQL ou QueryBuilder
        $request->query->getInt('page', 1), // Numéro de page
        10 // Nombre d'éléments par page
    );

    return $this->render('publication/showPublicationAdmin.html.twig', [
        'publications' => $publications,
        'searchQuery' => $searchQuery,
        
    ]);
}


    #[Route('/MesPublications', name: 'list_mes_publications')]
    public function listMesPublications(Request $request, EntityManagerInterface $entityManager, LikesRepository $likesRepository): Response
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
    
                // Retourner les erreurs sous forme de JSON
                return $this->json([
                    'success' => false,
                    'errors' => $errors,
                ]);
            } else {
                $publication->setDatePublication(new \DateTime());
                $user = $this->getUser();
                $publication->setUser($user);
    
                $entityManager->persist($publication);
    
                // --- Gestion des images ---
                $imageFiles = $form->get('images')->getData();
                if ($imageFiles) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/publication';
                    foreach ($imageFiles as $imageFile) {
                        if ($imageFile) {
                            // Validation du type de fichier (images seulement)
                            if (!in_array($imageFile->guessExtension(), ['jpg', 'png', 'jpeg', 'gif'])) {
                                // Retourner une erreur si le fichier n'est pas une image
                                return $this->json([
                                    'success' => false,
                                    'errors' => ['Seuls les fichiers images sont autorisés.'],
                                ]);
                            }
    
                            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                            try {
                                $imageFile->move($uploadDir, $newFilename);
                            } catch (FileException $e) {
                                // Gérer l'exception pour éviter des erreurs serveur visibles
                                return $this->json([
                                    'success' => false,
                                    'errors' => ['Erreur lors du téléchargement de l\'image.'],
                                ]);
                            }
    
                            $image = new Image();
                            $image->setUrl('/publication/' . $newFilename); // Chemin relatif
                            $image->setPublication($publication);
                            $entityManager->persist($image);
                        }
                    }
                }
    
                // Persister les images et publier la publication
                $entityManager->flush();
    
                // Retourner un succès avec redirection pour recharger
                return $this->json([
                    'success' => true,
                    'redirectUrl' => $this->generateUrl('list_publications'), 
                ]);
            }
        }
        $user = $this->getUser();
        $publications = $entityManager->getRepository(Publication::class)->findBy(['user' => $user], ['datePublication' => 'DESC']);
        

        // Gestion des likes pour chaque publication
        foreach ($publications as $pub) {
            $isLiked = $entityManager->getRepository(Likes::class)->findOneBy([
                'user' => $user,
                'publication' => $pub
            ]);
            $pub->isLiked = $isLiked !== null;
        }

        // Rendu de la page
        return $this->render('publication/showMesPublication.html.twig', [
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

    $user = $this->getUser(); // Assume user ID is 1
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
    return $this->render('publication/detailPublication.html.twig', [
        'publication' => $publication,
        'commentaires' => $commentaires,
        'formComment' => $formComment->createView(),
        'userOwner' => $user,
    ]);
}
#[Route('/publications/details/admin/{id}', name: 'detail_publications_admin')]
public function DetailsPublicationsAdmin($id, Request $request, EntityManagerInterface $entityManager, LikesRepository $likesRepository): Response
{
    $publication = $entityManager->getRepository(Publication::class)->find($id);
    if (!$publication) {
        throw $this->createNotFoundException('La publication n\'existe pas.');
    }

    

    $commentaires = $entityManager->getRepository(Commentaire::class)->findBy(['publication' => $publication], ['date' => 'DESC']);

    

    return $this->render('publication/detailPublicationAdmin.html.twig', [
        'publication' => $publication,
        'commentaires' => $commentaires,
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
#[Route('/publication/delete/{id}', name: 'delete_publication', methods: ['POST'])]
    public function deletePublication($id, EntityManagerInterface $entityManager): Response
    {
        $publication = $entityManager->getRepository(Publication::class)->find($id);

        if (!$publication) {
            return $this->json([
                'success' => false,
                'message' => 'Publication non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Suppression des images associées
        $images = $publication->getImages();
        foreach ($images as $image) {
            $entityManager->remove($image);
        }

        $entityManager->remove($publication);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Publication supprimée avec succès.'
        ]);
    }

    #[Route('/publication/update/{id}', name: 'update_publication', methods: ['POST'])]
public function updatePublication($id, Request $request, EntityManagerInterface $entityManager): Response
{
    $publication = $entityManager->getRepository(Publication::class)->find($id);

    if (!$publication) {
        return $this->json([
            'success' => false,
            'message' => 'Publication non trouvée.'
        ], Response::HTTP_NOT_FOUND);
    }

    $newContent = $request->request->get('contenu');

    if ($newContent !== null) { // Check if content was actually sent
        $publication->setContenu($newContent);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Publication mise à jour avec succès.'
        ]);
    } else {
         return $this->json([
            'success' => false,
            'message' => 'Aucun contenu fourni pour la mise à jour.'
        ]);
    }
}

#[Route('/comment/{id}', name: 'add_comment', methods: ['POST'])]
public function addComment($id, Request $request, EntityManagerInterface $entityManager): Response
{
    // Retrieve the publication based on the provided ID
    $publication = $entityManager->getRepository(Publication::class)->find($id);
    if (!$publication) {
        return $this->json([
            'success' => false,
            'errors' => ['Publication not found']
        ], Response::HTTP_NOT_FOUND);
    }

    // Create and handle the comment form
    $formComment = $this->createForm(CommentaireType::class);
    $formComment->handleRequest($request);

    // Check if the form is submitted and valid
    if ($formComment->isSubmitted()) {
        if ($formComment->isValid()) {
            $commentaire = $formComment->getData();
            $user = $this->getUser(); 
            
            $commentaire->setPublication($publication);
            $commentaire->setUser($user);
            $commentaire->setDate(new \DateTime());

            // Persist and flush the comment to the database
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                
            ]);
        } else {
            $errors = [];
            foreach ($formComment->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'success' => false,
            'errors' => $errors
        ]);
    }
    }
}
#[Route('/comment/delete/{id}', name: 'delete_comment', methods: ['POST'])]
public function deleteComment($id, EntityManagerInterface $entityManager): Response
{
    $comment = $entityManager->getRepository(Commentaire::class)->find($id);

    if (!$comment) {
        return $this->json([
            'success' => false,
            'message' => 'Commentaire non trouvé.'
        ], Response::HTTP_NOT_FOUND);
    }

    // Supprimer les likes associés au commentaire avant de le supprimer
    $likes = $entityManager->getRepository(LikesCommentaire::class)->findBy(['commentaire' => $comment]);
    foreach ($likes as $like) {
        $entityManager->remove($like);
    }

    $entityManager->remove($comment);
    $entityManager->flush();

    return $this->json([
        'success' => true,
        'message' => 'Commentaire supprimé avec succès.'
    ]);
}



} 
