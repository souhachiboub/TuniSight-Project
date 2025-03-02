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
use Symfony\Bundle\SecurityBundle\Security;


final class HomeController extends AbstractController
{
    /* ------ */
    #[Route('/', name: 'app_home', methods: ['GET'])]
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

}