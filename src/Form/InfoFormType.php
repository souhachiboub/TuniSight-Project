<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InfoFormType extends AbstractType
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
            ->add('numTel', TelType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\+?\d{9,15}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide avec un préfixe +'
                    ]),
                    new Assert\Length([
                        'min' => 9,
                        'minMessage' => 'Le numéro de téléphone doit contenir au moins {{ limit }} caractères'
                    ]),
                ],
            ])
            ->add('cin', TextType::class, [
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
            ->add('cartePro', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 50]),
                ],
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de naissance est obligatoire']),
                ],
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['min' => 5, 'max' => 100]),
                ],
            ])
            ->add('photoProfil', FileType::class, [
                'required' => false,
                'mapped' => false, // prevents Symfony from trying to store it as a string
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG/PNG)',
                    ]),
                ],
            ])
            ->add('bio', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
