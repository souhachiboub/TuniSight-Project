<?php

namespace App\Controller;

use App\Entity\Voucher;
use App\Entity\Activite;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Service\VoucherService;
use App\Repository\VoucherRepository;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }
  
    // #[Route('/reservation/{id}/apply-voucher/{codeVoucher}', name: 'apply_voucher', methods: ['POST'])]
    // public function applyVoucher(
    //     int $id,
    //     string $codeVoucher,
    //     ReservationRepository $reservationRepo,
    //     VoucherRepository $voucherRepo,
    //     VoucherService $voucherService
    // ): JsonResponse {
    //     $reservation = $reservationRepo->find($id);
    //     $voucher = $voucherRepo->findOneBy(['codeVoucher' => $codeVoucher]);

    //     if (!$reservation || !$voucher) {
    //         return $this->json(['message' => 'Réservation ou Voucher introuvable'], 404);
    //     }

    //     if ($voucherService->applyVoucher($reservation, $voucher)) {
    //         return $this->json(['message' => 'Voucher appliqué avec succès']);
    //     }

    //     return $this->json(['message' => 'Échec de l\'application du voucher'], 400);
    // }
//     #[Route('/newvoucher', name: 'app_reservation_new', methods: ['GET', 'POST'])]
// public function newReservation(
//     Request $request, 
//     EntityManagerInterface $entityManager, 
//     ActiviteRepository $activiteRepository,
//     VoucherRepository $voucherRepository
// ): Response {
//     $reservation = new Reservation();
//     $form = $this->createForm(ReservationType::class, $reservation);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         // Récupération du voucher depuis le formulaire
//         $codeVoucher = $request->request->get('voucher_code');
//         if ($codeVoucher) {
//             $voucher = $voucherRepository->findOneBy(['codeVoucher' => $codeVoucher]);
            
//             if (!$voucher) {
//                 $this->addFlash('danger', 'Le voucher est invalide.');
//             } elseif ($voucher->getIsUsed()) {
//                 $this->addFlash('warning', 'Ce voucher a déjà été utilisé.');
//             } elseif ($voucher->getDateExpiration() < new \DateTime()) {
//                 $this->addFlash('warning', 'Le voucher a expiré.');
//             } else {
//                 // Appliquer le voucher à la réservation
//                 $reservation->setVoucher($voucher);
//                 $voucher->setIsUsed(true);  // Marquer le voucher comme utilisé
//                 $entityManager->persist($voucher);
//             }
//         }

//         // Calculer le prix total avec ou sans réduction du voucher
//         $totalPrice = $reservation->getTotalPrice(); // La méthode de calcul du total

//         // Ajouter le prix total à la réservation
//         $reservation->setTotalPrice($totalPrice);

//         // Persister la réservation
//         $entityManager->persist($reservation);
//         $entityManager->flush();

//         // Rediriger après l'enregistrement
//         return $this->redirectToRoute('voucher_show');
//     }

//     return $this->render('reservation/apply_voucher.html.twig', [
//         'form' => $form->createView(),
//     ]);
// }
// ReservationController.php
// #[Route('/activity/apply-voucher/{id}', name: 'apply_voucher')]
// public function applyVoucher(
//     Request $request, 
//     Activite $activity, 
//     VoucherRepository $voucherRepository, 
//     ActiviteRepository $activiteRepository,
//     $id,
//     ReservationRepository $reservationRepository,
//     EntityManagerInterface $entityManager
// ): Response {
//     $user = $this->getUser(); // Get the logged-in user

//     // Find the reservation for the activity and user
//     $reservation = $reservationRepository->findOneBy([
//         'activite' => $activity,
//         'user' => $user
//     ]);

//     // Check if a reservation exists for the activity and user
//     if (!$reservation) {
//         $this->addFlash('danger', 'Vous n\'avez pas de réservation pour cette activité.');
//         return $this->redirectToRoute('activity_details', ['id' => $activity->getId()]);
//     }

//     // Create the form
//     $form = $this->createForm(ReservationType::class);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         $codeVoucher = $form->get('codeVoucher')->getData();
        
//         // Find the voucher by the code entered
//         $voucher = $voucherRepository->findOneBy(['codeVoucher' => $codeVoucher]);

//         // Check if the voucher is valid
//         if (!$voucher) {
//             $this->addFlash('danger', 'Code voucher invalide.');
//             return $this->redirectToRoute('apply_voucher', ['id' => $activity->getId()]);
//         }

//         // Check if the voucher has been used
//         if ($voucher->getIsUsed()) {
//             $this->addFlash('warning', 'Ce voucher a déjà été utilisé.');
//             return $this->redirectToRoute('apply_voucher', ['id' => $activity->getId()]);
//         }

//         // Check if the voucher has expired
//         if ($voucher->getDateExpiration() < new \DateTime()) {
//             $this->addFlash('warning', 'Ce voucher est expiré.');
//             return $this->redirectToRoute('apply_voucher', ['id' => $activity->getId()]);
//         }

//         // Calculate the new price after applying the discount
//         $originalPrice = $activity->getPrix();
//         $discount = $voucher->getValeurReduction();
//         $newPrice = $originalPrice - ($originalPrice * ($discount / 100));

//         // Apply the discount to the reservation and mark the voucher as used
//         $reservation->setTotalPrice(max($newPrice, 0)); // Ensure the price doesn't go below 0
//         $voucher->setIsUsed(true);

//         // Persist changes to the database
//         $entityManager->persist($reservation);
//         $entityManager->persist($voucher);
//         $entityManager->flush();

//         // Add success flash message
//         $this->addFlash('success', 'Votre voucher a été appliqué avec succès !');

//         // Redirect to the reservation details page
//         return $this->redirectToRoute('voucher_show');
//     }

//     // Render the form in the template
//     return $this->render('reservation/apply_voucher.html.twig', [
//         'form' => $form->createView(),
//         'activity' => $activity
//     ]);
// }


// public function applyVoucherToReservation(
//     int $activityId, 
//     ActiviteRepository $activiteRepo, 
//     ReservationRepository $reservationRepo, 
//     VoucherRepository $voucherRepo,  
//     VoucherService $voucherService, 
//     Request $request
// ): Response {
//     $activity = $activiteRepo->find($activityId);

//     if (!$activity) {
//         throw $this->createNotFoundException('Activity not found');
//     }

//     $reservation = $reservationRepo->findOneBy(['activity' => $activity]);

//     if (!$reservation) {
//         // Créer une nouvelle réservation si aucune n'existe
//         $reservation = new Reservation();
//         $reservation->setActivite($activity);
//     }

//     // ... Reste de la logique
// }

// #[Route('/apply-coupon', name: 'apply_coupon')]
//     public function applyCoupon(Request $request, EntityManagerInterface $entityManager): Response
//     {
//         $form = $this->createForm(ReservationType::class);
//         $form->handleRequest($request);

//         if ($form->isSubmitted() && $form->isValid()) {
//             $code = $form->get('codeVoucher')->getData();
//             $voucher = $entityManager->getRepository(Voucher::class)->findOneBy(['codeVoucher' => $code]);

//             if (!$voucher) {
//                 $this->addFlash('error', 'Ce coupon est invalide.');
//             } elseif ($voucher->getIsUsed()) {
//                 $this->addFlash('error', 'Ce coupon a déjà été utilisé.');
//             } elseif ($voucher->getDateExpiration() < new \DateTimeImmutable()) {
//                 $this->addFlash('error', 'Ce coupon a expiré.');
//             } else {
//                 // Appliquer la réduction (exemple)
//                 $this->addFlash('success', 'Coupon appliqué avec succès ! Réduction : ' . $voucher->getValeurReduction() . '%');
                
//                 // Marquer le coupon comme utilisé
//                 $voucher->setIsUsed(true);
//                 $entityManager->flush();
//             }
//         }

//         return $this->render('reservation/apply_voucher.html.twig', [
//             'voucherForm' => $form->createView(),
//         ]);
//     }

// #[Route('/apply-voucher/{id}', name: 'apply_voucher')]
// public function applyCoupon(int $id, Request $request, EntityManagerInterface $entityManager,SessionInterface $session): Response
// {
//     // Récupérer l'activité à partir de l'ID
//     $activity = $entityManager->getRepository(Activite::class)->find($id);

//     // Vérifier si l'activité existe
//     if (!$activity) {
//         $this->addFlash('error', 'L\'activité n\'a pas été trouvée.');
//         return $this->redirectToRoute('some_route'); // Redirigez vers une autre page si l'activité n'est pas trouvée
//     }

//     // Récupérer le prix initial de l'activité
//     $prixInitial = $activity->getPrix(); // Assurez-vous que getPrix() retourne le prix actuel de l'activité

//     $form = $this->createForm(ReservationType::class);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         $code = $form->get('codeVoucher')->getData();
//         $voucher = $entityManager->getRepository(Voucher::class)->findOneBy(['codeVoucher' => $code]);

//         if (!$voucher) {
//             $this->addFlash('error', 'Ce coupon est invalide.');
//         } elseif ($voucher->getIsUsed()) {
//             $this->addFlash('error', 'Ce coupon a déjà été utilisé.');
//         } elseif ($voucher->getDateExpiration() < new \DateTimeImmutable()) {
//             $this->addFlash('error', 'Ce coupon a expiré.');
//         } else {
            
//             $reduction = $voucher->getValeurReduction(); 
//             $prixAvecReduction = $prixInitial - ($prixInitial * $reduction / 100);

          
//             $activity->setPrix($prixAvecReduction);
//             $entityManager->flush(); 

//             $this->addFlash('success', 'Coupon appliqué avec succès ! Réduction : ' . $reduction . '% - Nouveau prix : ' . $prixAvecReduction . 'TND');
            

//             $voucher->setIsUsed(true);
//             $entityManager->flush();
//         }
//     }

//     return $this->render('reservation/apply_voucher.html.twig', [
//         'voucherForm' => $form->createView(),
//         'activity' => $activity, 
//         'userId' => $session->get('user_id'),
//         'nom' => $session->get('user_nom'),
//         'prenom' => $session->get('user_prenom'),
            
//     ]);
    
// }


#[Route('/load-voucher-form/{id}', name: 'load_voucher_form')]
public function loadVoucherForm(int $id, EntityManagerInterface $entityManager): Response
{
    // Récupérer l'activité à partir de l'ID
    $activity = $entityManager->getRepository(Activite::class)->find($id);

    if (!$activity) {
        throw $this->createNotFoundException('Activité non trouvée');
    }

    // Créer le formulaire
    $form = $this->createForm(ReservationType::class);

    // Renvoyer uniquement le formulaire (sans le layout complet)
    return $this->render('reservation/apply_voucher.html.twig', [
        'voucherForm' => $form->createView(),
        'activity' => $activity,
    ]);
}

#[Route('/apply-voucher/{id}', name: 'apply_voucher', methods: ['POST'])]
public function applyVoucher(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    // Récupérer l'activité à partir de l'ID
    $activity = $entityManager->getRepository(Activite::class)->find($id);

    if (!$activity) {
        return $this->json([
            'status' => 'error',
            'message' => 'L\'activité n\'a pas été trouvée.',
        ], Response::HTTP_NOT_FOUND);
    }

    // Récupérer le prix initial de l'activité
    $prixInitial = $activity->getPrix();

    // Créer et gérer le formulaire
    $form = $this->createForm(ReservationType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $code = $form->get('codeVoucher')->getData();
        $voucher = $entityManager->getRepository(Voucher::class)->findOneBy(['codeVoucher' => $code]);

        if (!$voucher) {
            return $this->json([
                'status' => 'error',
                'message' => 'Ce coupon est invalide.',
            ], Response::HTTP_BAD_REQUEST);
        } elseif ($voucher->getIsUsed()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Ce coupon a déjà été utilisé.',
            ], Response::HTTP_BAD_REQUEST);
        } elseif ($voucher->getDateExpiration() < new \DateTimeImmutable()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Ce coupon a expiré.',
            ], Response::HTTP_BAD_REQUEST);
        } else {
            // Appliquer la réduction
            $reduction = $voucher->getValeurReduction();
            $prixAvecReduction = $prixInitial - ($prixInitial * $reduction / 100);

            // Mettre à jour le prix de l'activité
            $activity->setPrix($prixAvecReduction);
            $entityManager->flush();

            // Marquer le voucher comme utilisé
            $voucher->setIsUsed(true);
            $entityManager->flush();

            // Renvoyer une réponse JSON de succès
            return $this->json([
                'status' => 'success',
                'message' => 'Coupon appliqué avec succès ! Réduction : ' . $reduction . '% - Nouveau prix : ' . $prixAvecReduction . 'TND',
                'newPrice' => $prixAvecReduction,
            ]);
        }
    }

    // Si le formulaire n'est pas valide, renvoyer les erreurs
    $errors = [];
    foreach ($form->getErrors(true) as $error) {
        $errors[] = $error->getMessage();
    }

    return $this->json([
        'status' => 'error',
        'message' => 'Le formulaire contient des erreurs.',
        'errors' => $errors,
    ], Response::HTTP_BAD_REQUEST);
}



}
