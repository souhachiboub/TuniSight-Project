<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Voucher;
use App\Form\VoucherType;
use Doctrine\ORM\Mapping\Id;
use App\Service\MailerService;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
    public function new(Request $request, EntityManagerInterface $entityManager,MailerService $mailer): Response
    {
        $voucher = new Voucher();
        $form = $this->createForm(VoucherType::class, $voucher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($voucher);
            $entityManager->flush();
            //Email sending 
            if ($voucher->getUser()) {
                // Envoyer un e-mail au client
                $mailer->sendVoucherEmail(
                    $voucher->getUser()->getEmail(),
                    $voucher->getCodeVoucher(),
                    $voucher->getValeurReduction(),
                    $voucher->getDateExpiration()
                );
            }

            $this->addFlash('success', 'Coupon créé avec succès !');
            return $this->redirectToRoute('voucher_show');
        }

        return $this->render('voucher/index.html.twig', [
            'voucherForm' => $form->createView(),
        ]);
    }
    #[Route('/vouchers', name: 'voucher_show')]
    public function show(VoucherRepository $voucherRepository): Response
    {
    return $this->render('voucher/show.html.twig', [
        'vouchers' => $voucherRepository->findAll(),
    ]);
    }

    #[Route('/voucher/{id}/edit', name: 'admin_voucher_edit')]
    public function edit(Request $request, Voucher $voucher, EntityManagerInterface $entityManager, MailerService $mailer): Response
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
    
        // Ajouter le champ "Sélectionner un client" si le voucher n'a pas d'utilisateur
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
    
            // Vérifier si un utilisateur a été assigné et envoyer un e-mail
            if ($voucher->getUser()) {
                $mailer->sendVoucherEmail(
                    $voucher->getUser()->getEmail(),
                    $voucher->getCodeVoucher(),
                    $voucher->getValeurReduction(),
                    $voucher->getDateExpiration()
                );
            }
    
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

    // #[Route('/assign/{id}', name: 'admin_voucher_assign')]
    // public function assigner(int $id, Request $request, VoucherRepository $voucherRepository,UserRepository $userRepository,EntityManagerInterface $entityManager,MailerService $mailer): Response {
    //     $voucher = $voucherRepository->find($id);
    //     if (!$voucher) {
    //         throw $this->createNotFoundException('Le coupon demandé n\'existe pas.');
    //     }
    //     $users = $userRepository->findAll();
    //     if ($request->isMethod('POST')) {
    //         $userId = $request->request->get('client_id');
    //         if ($userId) {
    //             $user = $userRepository->find($userId);
    //             if ($user) {
    //                 $voucher->setUser($user);
    //                 $entityManager->flush();
    //                 $this->addFlash('success', 'Coupon assigné avec succès !');
    //                 return $this->redirectToRoute('voucher_show');
    //             } else {
    //                 $this->addFlash('error', 'Utilisateur non trouvé.');
    //             }
    //         } else {
    //             $this->addFlash('error', 'Veuillez sélectionner un utilisateur.');
    //         }
    //     }
    //     return $this->render('voucher/assign.html.twig', [
    //         'voucher' => $voucher,
    //         'users' => $users 
    //     ]);
    // }
    
    


}
