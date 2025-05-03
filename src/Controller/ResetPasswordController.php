<?php
namespace App\Controller;

use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twilio\Rest\Client;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/request', name: 'reset_password_request')]
    public function request(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $this->entityManager->getRepository(UserEntity::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'No user found with this email.');
                return $this->redirectToRoute('reset_password_request');
            }

            // Generate a random 6-digit verification code
            $verificationCode = random_int(100000, 999999);
            $user->setVerificationCode($verificationCode);
            $this->entityManager->flush();

            // Send SMS via Twilio
            $this->sendSms($user->getNumTel(), $verificationCode);

            $this->addFlash('success', 'A verification code has been sent to your phone.');
            return $this->redirectToRoute('reset_password_verify', ['id' => $user->getId()]);
        }

        return $this->render('security/reset_password_request.html.twig');
    }

    #[Route('/verify/{id}', name: 'reset_password_verify')]
    public function verify(Request $request, UserEntity $user): Response
    {
        if ($request->isMethod('POST')) {
            $enteredCode = $request->request->get('verification_code');

            if ($enteredCode == $user->getVerificationCode()) {
                return $this->redirectToRoute('reset_password_reset', ['id' => $user->getId()]);
            } else {
                $this->addFlash('danger', 'Invalid verification code.');
            }
        }

        return $this->render('security/reset_password_verify.html.twig', ['user' => $user]);
    }

    #[Route('/reset/{id}', name: 'reset_password_reset')]
public function reset(
    int $id,
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager
): Response {
    $user = $entityManager->getRepository(UserEntity::class)->find($id);

    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }

    if ($request->isMethod('POST')) {
        $newPassword = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');

        if (empty($newPassword) || empty($confirmPassword)) {
            $this->addFlash('danger', 'Password fields cannot be empty.');
            return $this->redirectToRoute('reset_password_reset', ['id' => $id]);
        }

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('danger', 'Passwords do not match.');
            return $this->redirectToRoute('reset_password_reset', ['id' => $id]);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Password reset successfully.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/reset_password_reset.html.twig', [
        'userId' => $id,
    ]);
}

private function sendSms(string $phoneNumber, int $verificationCode): void
{
    $twilioSid = '';
    $twilioAuthToken = '';
    $twilioPhoneNumber = '';

    // Format phone number to ensure it includes the country code
    $phoneNumber = $this->formatPhoneNumber($phoneNumber);

    // Debug: Verify the formatted number before sending
    //dump("Sending SMS to: " . $phoneNumber);
    //die(); // Stop execution to verify the number in Symfony debug

    $client = new Client($twilioSid, $twilioAuthToken);
    $client->messages->create(
        $phoneNumber,
        [
            'from' => $twilioPhoneNumber,
            'body' => "Your password reset code is: $verificationCode"
        ]
    );
    
}






private function formatPhoneNumber(string $phoneNumber): string
{
    // Remove any non-numeric characters
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Ensure the phone number starts with the country code (+216 for Tunisia)
    if (!str_starts_with($phoneNumber, '+216')) {
        $phoneNumber = '+216' . ltrim($phoneNumber, '0');  // Remove leading 0 if present
    }

    return $phoneNumber;
}









}
