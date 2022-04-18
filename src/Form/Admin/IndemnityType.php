<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Indemnity;
use App\Form\HiddenBikeRideTypeType;
use App\Form\HiddenLevelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class IndemnityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ->add('level', HiddenLevelType::class)
            ->add('bikeRideType', HiddenBikeRideTypeType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Indemnity::class,
        ]);
    }
}
