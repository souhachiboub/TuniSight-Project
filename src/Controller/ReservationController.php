<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Stripe\Stripe;
use App\Entity\Voucher;
use App\Entity\Activite;
use App\Entity\Reservation;
use Stripe\Checkout\Session;
use App\Form\ReservationType;
use App\Service\QRCodeService;
use App\Service\VoucherService;
use Endroid\QrCode\Writer\PngWriter;
use App\Repository\VoucherRepository;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Endroid\QrCode\QrCode;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }




    /* - --------------------  - - */


    #[Route('/paiement', name: 'payment')]
    public function indexx(Request $request): Response
    {
        $activiteId = $request->request->get('activiteId');
        $totalPrice = $request->request->get('totalPrice');
        $nbrPersonnes = $request->request->get('nbrPersonnes');

        return $this->render('paiement/paiement-reservation.html.twig', [
            'totalPrice' => $totalPrice,
            'activiteId' => $activiteId,
            'nbrPersonnes' => $nbrPersonnes
        ]);
    }


    #[Route('/checkout', name: 'checkout')]
    public function checkout(
        $stripeSK,
        Security $security,
        Request $request,
        SessionInterface $session
    ): Response {
        Stripe::setApiKey($stripeSK);
        $user = $security->getUser();
        $totalPrice = $request->request->get('totalPrice');
        $nbrPersonnes = $request->request->get('nbrPersonnes');
        $activiteId = $request->request->get('activiteId');

        $session->set('reservation_data', [
            'totalPrice' => $totalPrice,
            'nbrPersonnes' => $nbrPersonnes,
            'activiteId' => $activiteId,
        ]);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email'       => $user->getUserIdentifier(),
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => 'usd',
                        'product_data' => [
                            'name' => 'Réservation d\'activité',
                            'description' => 'Prix total pour la réservation',
                        ],
                        'unit_amount'  => $totalPrice * 100,
                    ],
                    'quantity'   => 1,
                ]
            ],
            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('success_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $this->generateUrl('cancel_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'payment_intent_data' => [

                'metadata' => [
                    'user_id' => $user->getUserIdentifier(),
                    'nbrPersonnes' => $nbrPersonnes,
                    'activiteId'   => $activiteId,
                ],
            ],
        ]);

        return $this->redirect($session->url, 303);
    }


    #[Route('/success-url', name: 'success_url')]
    public function successUrl(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
    ): Response {
        $reservationData = $session->get('reservation_data');
        if (!$reservationData) {
            return new Response('Données manquantes.', Response::HTTP_BAD_REQUEST);
        }

        $totalPrice = $reservationData['totalPrice'];
        $nbrPersonnes = $reservationData['nbrPersonnes'];
        $activiteId = $reservationData['activiteId'];

        $user = $security->getUser();

        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setNbrPersonnes($nbrPersonnes);
        $reservation->setPrixTotal($totalPrice);

        $activite = $entityManager->getRepository(Activite::class)->find($activiteId);
        if ($activite) {
            $reservation->setActivite($activite);
        } else {
            return new Response('Activité non trouvée.', Response::HTTP_NOT_FOUND);
        }

        $entityManager->persist($reservation);
        $entityManager->flush();

        $reservationDetails = sprintf(
            "Réservation ID : %d\nUtilisateur : %s\nNombre de personnes : %d\nPrix total : %.2f TND \nActivité : %s",
            $reservation->getId(),
            $user->getUserIdentifier(),
            $reservation->getNbrPersonnes(),
            $reservation->getPrixTotal(),
            $activite ? $activite->getLibelle() : 'N/A'
        );

        $qrCode = new QrCode($reservationDetails);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $qrCodePath = $this->getParameter('kernel.project_dir') . '/public/qrcodes/reservation_' . $reservation->getId() . '.png';
        file_put_contents($qrCodePath, $result->getString());

        return $this->render('paiement/success.html.twig', [
            'reservation' => $reservation,
            'qrCodeImage' => $result->getDataUri(),
        ]);
    }




    #[Route('/cancel-url', name: 'cancel_url')]
    public function cancelUrl(): Response
    {
        return $this->render('paiement/cancel.html.twig', []);
    }

    /* facturee */
    #[Route('/generate-invoice/{id}', name: 'generate_invoice')]
    public function generateInvoice(Reservation $reservation): Response
    {
        $html = $this->renderView('paiement/facture.html.twig', [
            'reservation' => $reservation,
        ]);

        $dompdf = new Dompdf();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();


        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture-' . $reservation->getId() . '.pdf"',
            ]
        );
    }


    #[Route('/mes-reservations', name: 'mes_reservations')]
    public function mesReservations(Security $security, EntityManagerInterface $entityManager)
    {
        $user = $security->getUser();
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(['user' => $user]);

        return $this->render('paiement/mes-reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }



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

    #[Route('/apply-voucher/{id}', name: 'apply_voucher', methods: ['GET', 'POST'])]
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
            } elseif ($voucher->getisAssigned()) {
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
                $voucher->setIsAssigned(true);
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
