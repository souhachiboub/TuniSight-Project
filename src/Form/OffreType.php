<?php

namespace App\Form;

use App\Entity\Offre;
use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder
            ->add('reduction',IntegerType::class, [
                'label' => 'Réduction (%)'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Emission',
                'required' => false,
                'empty_data' => null,
                'attr' => [
                'min' => (new \DateTime())->format('Y-m-d'),
                 ],
            ])
            ->add('dateExpiration',DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Expiration',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('activitie', EntityType::class, [
                'class' => Activite::class, // Spécifier l'entité associée
                'choice_label' => 'libelle', // Afficher le nom de l'activité dans la liste déroulante
                'label' => 'Sélectionner l\'activité',
                'placeholder' => 'Choisir une activité', // Optionnel, permet d'ajouter un message placeholder
                'disabled' => $options['is_edit'], 
        ])
       
           
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
            'is_edit' => false,
            
        ]);
    }
}
