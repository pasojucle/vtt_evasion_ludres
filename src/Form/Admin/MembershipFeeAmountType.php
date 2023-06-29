<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\MembershipFeeAmount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipFeeAmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'scale' => 2,
                'attr' => [
                    'class' => 'align-right',
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('coverage', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembershipFeeAmount::class,
        ]);
    }
}
