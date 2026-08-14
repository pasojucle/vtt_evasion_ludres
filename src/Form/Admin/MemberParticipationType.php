<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Filter\MemberParticipationFilter;
use App\Entity\BikeRideType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateType::class, [
                    'label' => false,
                    'attr' => [
                        'data-action' => 'change->filter-tab#submit'
                    ],
                ])
            ->add('endAt', DateType::class, [
                    'label' => false,
                    'attr' => [
                        'data-action' => 'change->filter-tab#submit'
                    ],
                ])
            ->add('type', EntityType::class, [
                    'label' => false,
                    'class' => BikeRideType::class,
                    'required' => false,
                    'placeholder' => 'Sélectionnez un type d\'activité',
                    'attr' => [
                        'data-action' => 'change->filter-tab#submit'
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberParticipationFilter::class,
        ]);
    }
}
