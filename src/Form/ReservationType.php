<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Voucher;
use App\Entity\Activite;
use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('codeVoucher', TextType::class, [ // Remplace 'codeVoucher' par 'codeVoucher'
            'mapped' => false, // Ce champ ne fait pas partie de l'entité Reservation
            'label' => 'Code du voucher',
            'attr' => [
                'placeholder' => 'Entrez votre code de réduction',
                'class' => 'form-control'
            ]
        ])
        ->add('apply', SubmitType::class, [
            'label' => 'Appliquer le voucher',
            'attr' => ['class' => 'btn btn-primary']
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
