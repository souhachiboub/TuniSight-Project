<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use App\Repository\CategorieActiviteRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/activite')]
final class ActiviteController extends AbstractController
{
    #[Route('/list', name: 'app_index', methods: ['GET'])]
    public function index(
        ActiviteRepository $activiteRepository,
        SessionInterface $session,
        CategorieActiviteRepository $categorieRepository,
        PaginatorInterface $paginator,
        Request $request,
        VilleRepository $villeRepository
    ): Response {
        $categorieId = $request->query->get('categorie', null);

        // Créer une requête QueryBuilder pour les activités
        $queryBuilder = $activiteRepository->createQueryBuilder('a');

        // Filtrer par catégorie si une catégorie est sélectionnée
        if ($categorieId) {
            $queryBuilder->andWhere('a.categorie = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Passer la requête au paginator
            $request->query->getInt('page', 1), // Numéro de page (1 par défaut)
            2// Nombre d'éléments par page
        );

        $categories = $categorieRepository->findAll();
        $villes = $villeRepository->findAll();

        return $this->render('FrontOffice-activites/index.html.twig', [
            'villes' => $villes,
            'pagination' => $pagination,
            'userId' => $session->get('user_id'),
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
            'categories' => $categories,
        ]);
    }


    #[Route('/decouvrir', name: 'app_activite_index', methods: ['GET'])]
    public function decouvrirActivite(
        ActiviteRepository $activiteRepository,
        SessionInterface $session,
        CategorieActiviteRepository $categorieRepository,
        Request $request,
        VilleRepository $villeRepository
    ): Response {

        $activites = $activiteRepository->findAll();
        $categories = $categorieRepository->findAll();



        // Récupérer toutes les villes
        $villes = $villeRepository->findAll();

        // Renvoyer la réponse à la vue
        return $this->render('FrontOffice-activites/decouvrirActivites.html.twig', [
            'userId' => $session->get('user_id'),
            'activites' => $activites,
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
            'categories' => $categories,
            'villes' => $villes,
        ]);
    }

    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
    public function newActivite(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activite->setDuree();
            $entityManager->persist($activite);
            $entityManager->flush();

            return $this->redirectToRoute('app_activite_index');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('activite/_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        return $this->render('activite/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'app_activite_indexx', methods: ['GET'])]
    public function indexx(ActiviteRepository $activiteRepository): Response
    {
        return $this->render('activite/index.html.twig', [
            'activites' => $activiteRepository->findAll(),
        ]);
    }

    #[Route('/filter', name: 'app_activite_filter', methods: ['POST'])]
    public function filter(Request $request, ActiviteRepository $activiteRepository): Response
    {
        // Récupère les paramètres du formulaire
        $categorieId = $request->request->get('categorie'); // Peut être une chaîne vide
        $villeId = $request->request->get('ville'); // Peut être une chaîne vide
        $prixMin = $request->request->get('prixMin'); // Peut être null ou une chaîne vide
        $prixMax = $request->request->get('prixMax'); // Peut être null ou une chaîne vide

        // Convertit les prix en float si nécessaire
        $prixMin = $prixMin !== null && $prixMin !== '' ? (float) $prixMin : null;
        $prixMax = $prixMax !== null && $prixMax !== '' ? (float) $prixMax : null;

        // Appelle la méthode findByFilters pour obtenir les activités filtrées
        $activites = $activiteRepository->findByFilters($categorieId, $villeId, $prixMin, $prixMax);

        // Rend le template partiel avec les activités filtrées
        return $this->render('FrontOffice-activites/_list.html.twig', [
            'activites' => $activites,
        ]);
    }

    #[Route('/{id}', name: 'app_activite_show', methods: ['GET'])]
    public function show(Activite $activite, SessionInterface $session): Response
    {
        return $this->render('FrontOffice-activites/detailsActivite.html.twig', [
            'activite' => $activite,
            'userId' => $session->get('user_id'),
            'nom' => $session->get('user_nom'),
            'prenom' => $session->get('user_prenom'),
        ]);
    }

    #[Route('{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activite->setDuree();
            $entityManager->flush();
            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('BackOffice-activites/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_activite_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $activite = $entityManager->getRepository(Activite::class)->find($id);

        if (!$activite) {
            throw new NotFoundHttpException('Activité non trouvée.');
        }

        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $id, $token)) {
            $imagePath = $activite->getImage();
            if ($imagePath && !empty($imagePath)) {
                $filesystem = new Filesystem();
                $imageFullPath = $this->getParameter('kernel.project_dir') . '/public/uploads/activites/' . $imagePath;
                if ($filesystem->exists($imageFullPath)) {
                    $filesystem->remove($imageFullPath);
                }
            }

            $entityManager->remove($activite);
            $entityManager->flush();
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide.');
        }
        return $this->redirectToRoute('app_activite_index');
    }
}
