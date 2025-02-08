<?php

namespace App\Controller;

use App\Entity\Voucher;
use App\Form\VoucherType;
use App\Repository\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            return $this->redirectToRoute('voucher_show');
        }

        return $this->render('voucher/index.html.twig', [
            'voucherForm' => $form->createView(),
        ]);
    }


    #[Route('/vouchers', name: 'voucher_show')]
    public function show(VoucherRepository $offreRepository): Response
    {
    return $this->render('voucher/show.html.twig', [
        'vouchers' => $offreRepository->findAll(),
    ]);
    }

    #[Route('/voucher/{id}/edit', name: 'admin_voucher_edit')]
    public function edit(Request $request, Voucher $voucher, EntityManagerInterface $entityManager): Response
    {
    // Création du formulaire avec uniquement les champs nécessaires
    $form = $this->createFormBuilder($voucher)
        ->add('dateExpiration', DateType::class, [
            'widget' => 'single_text',
            'required' => true,
            'label' => 'Nouvelle date d\'expiration'
        ])
        ->add('valeurReduction', IntegerType::class, [
            'required' => true,
            'label' => 'Nouvelle valeur de réduction (%)'
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Enregistrer les modifications'
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        $this->addFlash('success', 'Coupon mis à jour avec succès !');
        return $this->redirectToRoute('voucher_show');
    }

    return $this->render('voucher/edit.html.twig', [
        'voucherForm' => $form->createView(),
    ]);
    }

    #[Route('/voucher/{id}/delete', name: 'admin_voucher_delete')]
    public function delete(Request $request, Voucher $voucher, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si le token CSRF est valide pour la suppression
        if ($this->isCsrfTokenValid('delete' . $voucher->getId(), $request->request->get('_token'))) {
            
            // Si le voucher a un utilisateur associé, on dissocie l'utilisateur avant la suppression
            if ($voucher->getUser()) {
                $voucher->setUser(null);  // Dissocier l'utilisateur du voucher
                $entityManager->persist($voucher); // Persister l'entité mise à jour
            }
    
            // Supprimer le voucher
            $entityManager->remove($voucher);
            
            // Sauvegarder les changements dans la base de données
            $entityManager->flush();
    
            // Ajouter un message flash pour informer de la réussite
            $this->addFlash('success', 'Coupon supprimé avec succès !');
        }
    
        // Rediriger vers la page des vouchers après la suppression
        return $this->redirectToRoute('voucher_show');
    }
    


}
