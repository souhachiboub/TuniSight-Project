<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Voucher;
use App\Form\VoucherType;
use App\Service\MailerService;
use App\Repository\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    #[Route('/new', name: 'voucher_new')]
    public function new(Request $request, EntityManagerInterface $entityManager,MailerService $mailer): Response
    {
        $voucher = new Voucher();
        $form = $this->createForm(VoucherType::class, $voucher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($voucher);
            $entityManager->flush();
            if ($voucher->getUser()) {
                $mailer->sendVoucherEmail(
                    $voucher->getUser()->getEmail(),
                    $voucher->getCodeVoucher(),
                    $voucher->getValeurReduction(),
                    $voucher->getDateExpiration()
                );
            }

            return $this->redirectToRoute('voucher_show');
        }

        return $this->render('voucher/index.html.twig', [
            'voucherForm' => $form->createView(),
        ]);
    }
    // #[Route('/vouchers', name: 'voucher_show')]
    // public function show(VoucherRepository $voucherRepository): Response
    // {
    // return $this->render('voucher/show.html.twig', [
    //     'vouchers' => $voucherRepository->findAll(),
    // ]);
    // }


    #[Route('/vouchers', name: 'voucher_show', methods: ['GET', 'POST'])]
    public function show(Request $request, VoucherRepository $voucherRepository): Response
    {
        // Récupérer les filtres depuis la requête GET
        $expired = $request->query->get('expired', '');
        $assigned = $request->query->get('assigned', '');
    
        // Convertir les valeurs en booléen ou null
        $expired = ($expired === '') ? null : filter_var($expired, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $assigned = ($assigned === '') ? null : filter_var($assigned, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    
        // Filtrer les vouchers
        $vouchers = $voucherRepository->filterVouchers($expired, $assigned);
    
        return $this->render('voucher/show.html.twig', [
            'vouchers' => $vouchers,
            'expired' => $expired,
            'assigned' => $assigned
        ]);
    }
    
    #[Route('/voucher/edit/{id}', name: 'voucher_edit')]
    public function edit(Request $request, Voucher $voucher, EntityManagerInterface $entityManager,MailerService $mailer): Response
    {
        $formBuilder = $this->createFormBuilder($voucher)
            ->add('dateExpiration', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Nouvelle date d\'expiration'
            ])
            ->add('valeurReduction', IntegerType::class, [
                'required' => true,
                'label' => 'Nouvelle valeur de réduction (%)'
            ]);
        if (!$voucher->getUser()) {
            $formBuilder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getEmail() ;
                },
                'placeholder' => 'Sélectionnez un client',
                'required' => false,
                'label' => 'Assigner à un client'
            ]);
        }    
        $formBuilder->add('save', SubmitType::class, [
            'label' => 'Enregistrer les modifications'
        ]);    
        $form = $formBuilder->getForm();
        $form->handleRequest($request);   
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();           
            if ($voucher->getUser()) {
                $mailer->sendVoucherEmail(
                    $voucher->getUser()->getEmail(),
                    $voucher->getCodeVoucher(),
                    $voucher->getValeurReduction(),
                    $voucher->getDateExpiration()
                );
            }    
            $this->addFlash('success', 'Voucher mis à jour avec succès !');
            return $this->redirectToRoute('voucher_show');
        }   
        return $this->render('voucher/edit.html.twig', [
            'voucherForm' => $form->createView(),
        ]);
    }
    
    #[Route('/voucher/delete/{id}', name: 'voucher_delete')]
    public function delete(Request $request, Voucher $voucher, EntityManagerInterface $entityManager): Response
    {
       
        if ($this->isCsrfTokenValid('delete' . $voucher->getId(), $request->request->get('_token'))) {
            if ($voucher->getUser()) {
                $voucher->setUser(null);  
                $entityManager->persist($voucher); 
            }
            $entityManager->remove($voucher);
            $entityManager->flush();
        }
        return $this->redirectToRoute('voucher_show');
    }

    #[Route('/vouchers/filter', name: 'voucher_filter', methods: ['GET', 'POST'])]

   
    public function filterVouchers(Request $request, VoucherRepository $voucherRepository): Response
    {
    $form = $this->createFormBuilder()
        ->add('expired', ChoiceType::class, [
            'choices' => [
                'Tous' => null,
                'Expirés' => true,
                'Non expirés' => false,
            ],
            'required' => false,
            'expanded' => true,
            'multiple' => false
        ])
        ->add('assigned', ChoiceType::class, [
            'choices' => [
                'Tous' => null,
                'Assignés' => true,
                'Non assignés' => false,
            ],
            'required' => false,
            'expanded' => true,
            'multiple' => false
        ])
        ->add('submit', SubmitType::class, ['label' => 'Filtrer'])
        ->getForm();

        $form->handleRequest($request);

        $filters = $form->getData() ?? []; // Ensure $filters is an array
        
        $expired = $filters['expired'] ?? null; // Default to null if not set
        $assigned = $filters['assigned'] ?? null; // Default to null if not set
        
        $vouchers = $voucherRepository->filterVouchers($expired, $assigned);
        

    return $this->render('voucher/filltrage.html.twig', [
        'form' => $form->createView(),
        'vouchers' => $vouchers
    ]);
    }

    


}