<?php

namespace App\Controller;

use App\Entity\Voucher;
use App\Form\VoucherType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoucherController extends AbstractController
{
    #[Route('/voucher', name: 'app_voucher')]
    public function index(): Response
    {
        return $this->render('voucher/index.html.twig', [
            'controller_name' => 'VoucherController',
        ]);
    }

    #[Route('/new', name: 'admin_voucher_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $voucher = new Voucher();
        $form = $this->createForm(VoucherType::class, $voucher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($voucher);
            $entityManager->flush();

            $this->addFlash('success', 'Coupon créé avec succès !');
            return $this->redirectToRoute('admin_voucher_new');
        }

        return $this->render('voucher/index.html.twig', [
            'voucherForm' => $form->createView(),
        ]);
    }
}
