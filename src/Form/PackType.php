<?php

namespace App\Form;

use App\Entity\Pack;
use App\Entity\Offre;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class)
        ->add('reductionTotal', NumberType::class)  // Make sure to add this field
        ->add('produits', EntityType::class, [
            'class' => Produit::class,
            'choice_label' => 'name',  // adjust as needed
            'multiple' => true,        // Allows multiple products
            'expanded' => true,        // Whether to use checkboxes or not
             ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer'
            ]);
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pack::class,
        ]);
    }
}
