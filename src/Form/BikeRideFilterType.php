<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\BikeRide;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BikeRideFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(BikeRide::PERIODS),
                'attr' => [
                    'class' => 'btn',
                ],
            ])
            ->add('date', HiddenType::class)
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $bikeRide) {
            $filters = $bikeRide->getData();
            $form = $bikeRide->getForm();
            if (!in_array($filters['period'], [BikeRide::PERIOD_ALL, BikeRide::PERIOD_NEXT], true)) {
                $form
                    ->add('prev', SubmitType::class, [
                        'label' => '<i class="fas fa-angle-left"></i>',
                        'label_html' => true,
                        'attr' => [
                            'class' => 'btn btn-default',
                            'title' => 'Précedent',
                        ],
                    ])
                    ->add('next', SubmitType::class, [
                        'label' => '<i class="fas fa-angle-right"></i>',
                        'label_html' => true,
                        'attr' => [
                            'class' => 'btn btn-default',
                            'title' => 'Suivant',
                        ],
                    ])
                    ->add('today', SubmitType::class, [
                        'label' => 'Aujourd\'hui',
                        'attr' => [
                            'class' => 'btn btn-default',
                            'title' => 'Aujourd\'hui',
                        ],
                    ])
                ;
            }
        });
    }
}
