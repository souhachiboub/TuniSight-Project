<?php

namespace App\Form;

use App\Entity\CategorieActivite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CategorieActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['required' => true],
                'label' => 'Nom',
                'help' => 'Le nom doit contenir entre 3 et 50 caractères.',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'help' => 'Maximum 255 caractères.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategorieActivite::class,
        ]);
    }
}
