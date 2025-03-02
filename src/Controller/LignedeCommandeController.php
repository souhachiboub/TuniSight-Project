<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LignedeCommandeController extends AbstractController
{
    #[Route('/lignede/commande', name: 'app_lignede_commande')]
    public function index(): Response
    {
        return $this->render('lignede_commande/index.html.twig', [
            'controller_name' => 'LignedeCommandeController',
        ]);
    }
}
