<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Voucher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class VoucherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('codeVoucher', null, [
            'disabled' => true, // Le code est généré automatiquement
        ])
        ->add('dateEmission', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de début'
        ])
        ->add('dateExpiration',DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de début'
        ])
        ->add('valeurReduction',IntegerType::class, [
            'label' => 'Réduction (%)'
        ])
        ->add('isUsed', CheckboxType::class, [
            'label' => 'Coupon utilisé',
            'required' => false,
        ])
        ->add('user', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'email', // Sélection par email
            'label' => 'Assigné à (Client)',
            'required' => false,
            'placeholder' => 'Aucun', // Option pour ne pas assigner
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Enregistrer'
        ]);
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voucher::class,
        ]);
    }
}
