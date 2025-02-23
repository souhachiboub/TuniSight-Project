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

        $queryBuilder = $activiteRepository->createQueryBuilder('a');

        if ($categorieId) {
            $queryBuilder->andWhere('a.categorie = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            4
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

    #[Route('/', name: 'app_activite_backoffice', methods: ['GET'])]
    public function indexx(ActiviteRepository $activiteRepository): Response
    {
        return $this->render('BackOffice-activites/index.html.twig', [
            'activites' => $activiteRepository->findAll(),
        ]);
    }

    #[Route('/decouvrir', name: 'app_activite_index', methods: ['GET'])]
    public function decouvrirActivites(
        ActiviteRepository $activiteRepository,
        SessionInterface $session,
        CategorieActiviteRepository $categorieRepository,
        Request $request,
        VilleRepository $villeRepository
    ): Response {

        $activites = $activiteRepository->findAll();
        $categories = $categorieRepository->findAll();



        $villes = $villeRepository->findAll();

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

            return $this->redirectToRoute('app_activite_backoffice');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('BackOffice-activites/_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        return $this->render('BackOffice-activites/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/filter', name: 'app_activite_filter', methods: ['POST'])]
    public function filter(Request $request, ActiviteRepository $activiteRepository): Response
    {
        $categorieId = $request->request->get('categorie'); // Peut être une chaîne vide
        $villeId = $request->request->get('ville'); // Peut être une chaîne vide
        $prixMin = $request->request->get('prixMin'); // Peut être null ou une chaîne vide
        $prixMax = $request->request->get('prixMax'); // Peut être null ou une chaîne vide

        $prixMin = $prixMin !== null && $prixMin !== '' ? (float) $prixMin : null;
        $prixMax = $prixMax !== null && $prixMax !== '' ? (float) $prixMax : null;

        $activites = $activiteRepository->findByFilters($categorieId, $villeId, $prixMin, $prixMax);

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
            return $this->redirectToRoute('app_activite_backoffice');
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
        return $this->redirectToRoute('app_activite_backoffice');
    }
}
