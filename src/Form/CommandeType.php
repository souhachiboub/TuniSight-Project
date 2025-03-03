<?php


namespace App\Form;


use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('adresseliv', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['class' => 'form-control']
            ])
            ->add('numerotelephone', TextType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['class' => 'form-control']
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'attr' => ['novalidate' => 'novalidate'], // Désactive la validation HTML5
        ]);
    }
}
