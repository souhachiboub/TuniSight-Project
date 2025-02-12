<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\CategorieActivite;
use App\Entity\Offre;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('description')
            #->add('imageFile', VichImageType::class, [
            #   'required' => false,
            #   'label' => 'Image',
            #   'mapped' => true,
            #   'download_uri' => false,
            #  'image_uri' => false,
            #])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'mapped' => true,

            ])
            ->add('prix')
            ->add('duree')
            ->add('localisation')
            ->add('capacite')
            ->add('disponibilite')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])


            #->add('ville', EntityType::class, [
            #   'class' => Ville::class,
            #   'choice_label' => 'id',
            #  'multiple' => true,
            #])
            ->add('categorie', EntityType::class, [
                'class' => CategorieActivite::class,
                'choice_label' => 'nom',

            ])
            ->add('offre', EntityType::class, [
                'class' => Offre::class,
                'choice_label' => 'id',
            ])

            #->add('reservation', EntityType::class, [
            #   'class' => Reservation::class,
            #   'choice_label' => 'id',
            #   'multiple' => true,
            #])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }
}
