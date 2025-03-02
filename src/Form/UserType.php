<?php

namespace App\Form;

use App\Entity\DemandePrestataire;
use App\Entity\User;
use App\Entity\Voucher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length([
                        'min' => 2, 'max' => 50,
                        'minMessage' => 'Le nom doit avoir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
                    new Assert\Length(['min' => 2, 'max' => 50]),
                ],
            ])
            
            
            ->add('cin', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CIN est obligatoire']),
                    new Assert\Length([
                        'min' => 8, 'max' => 8,
                        'exactMessage' => 'Le CIN doit contenir exactement {{ limit }} chiffres'
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
                    new Assert\Email(['message' => 'Veuillez entrer une adresse email valide']),
                ],
            ])
            ->add('motdepasse', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire']),
                    new Assert\Length([
                        'min' => 6,
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('confirmpwd', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'mapped' => false, // Ne sera pas enregistré en base
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez confirmer votre mot de passe.']),
                ],
            ])
            
            
            
            
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de naissance est obligatoire']),
                    new Assert\LessThanOrEqual([
                        'value' => new \DateTime('-18 years'),
                        'message' => 'Vous devez avoir au moins 18 ans pour vous inscrire.',
                    ]),
                ],
            ])
           
            
            
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Prestataire' => 'prestataire',
                    'Artisant' => 'artisant',
                ],
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir un rôle']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * Custom validator to check if the user is over 18 years old
     */
    public function validateAge($value, ExecutionContextInterface $context)
    {
        $now = new \DateTime();
        $age = $now->diff($value)->y;

        if ($age < 18) {
            $context->buildViolation('L\'utilisateur doit avoir au moins 18 ans')
                ->addViolation();
        }
    }
}