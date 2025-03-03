<?php

namespace App\Controller;

use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted("IS_AUTHENTICATED_FULLY")]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'user_profile', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $numTel = $request->request->get('numTel');
            $adresse = $request->request->get('adresse');

            // ✅ Check if values exist before setting them
            if (!empty($nom)) $user->setNom($nom);
            if (!empty($prenom)) $user->setPrenom($prenom);
            if (!empty($numTel)) $user->setNumTel($numTel);
            if (!empty($adresse)) $user->setAdresse($adresse);

            /** @var UploadedFile $photoFile */
            $photoFile = $request->files->get('photoProfil');

            if ($photoFile) {
                $uploadsDir = $this->getParameter('profile_pictures_directory');
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($uploadsDir, $newFilename);
                    $user->setPhotoProfil($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/profile/change-password', name: 'change_password', methods: ['POST'])]
public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException('You must be logged in to change your password.');
    }

    $oldPassword = $request->request->get('old_password');
    $newPassword = $request->request->get('new_password');
    $confirmPassword = $request->request->get('confirm_password');

    // Check if the old password is correct
    if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
        $this->addFlash('danger', '⚠️ Ancien mot de passe incorrect.');
        return $this->redirectToRoute('user_profile');
    }

    // Check if new passwords match
    if ($newPassword !== $confirmPassword) {
        $this->addFlash('danger', '⚠️ Les nouveaux mots de passe ne correspondent pas.');
        return $this->redirectToRoute('user_profile');
    }

    // Hash and save the new password
    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
    $user->setPassword($hashedPassword);
    $entityManager->flush();

    $this->addFlash('success', '✅ Mot de passe changé avec succès.');
    return $this->redirectToRoute('user_profile');
}

    





}
