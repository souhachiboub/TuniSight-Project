<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\CategorieProduit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('libelle', TextType::class, [
            'attr' => ['class' => 'form-control'],
            'empty_data' => '',  // Si le champ est vide, cela renvoie une chaîne vide au lieu de null
            'constraints' => [
                new NotBlank(['message' => '']),
                new Length([
                    'max' => 255,
                    'maxMessage' => '',
                ]),
            ],
        ])            ->add('description', TextType::class)
            ->add('prix', NumberType::class)
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit (PNG, JPG)',
                'mapped' => false,  // Ce champ n'est pas lié à l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                    ])
                ],
            ])
            ->add('categorieProduit', EntityType::class, [
                'class' => CategorieProduit::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner une catégorie',
            ]);

        if (!$options['is_edit']) {
            $builder
                ->add('quantite', NumberType::class)
              ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'is_edit' => false,
           'csrf_protection' => true, // Désactive la protection CSRF

        ]);
    }
}
