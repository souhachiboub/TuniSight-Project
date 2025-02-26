<?php

namespace App\Form;

use App\Entity\UserEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class)
        ->add('role', ChoiceType::class, [
            'choices' => [
                'Utilisateur' => 'ROLE_USER',
                'Prestataire' => 'ROLE_PRESTATAIRE',
                'Artisan' => 'ROLE_ARTISAN',
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])

        ->add('confirmPassword', PasswordType::class, [
            'mapped' => false,
            'label' => 'Confirm Password', // Label for the field
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please confirm your password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password confirmation should be at least {{ limit }} characters',
                    'max' => 4096,
                ]),
                
            ],
        ])
        ->add('submit', SubmitType::class, ['label' => 'S\'inscrire']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserEntity::class,
        ]);
    }
}
