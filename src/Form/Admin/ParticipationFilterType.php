<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRideType;
use App\Service\SeasonService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ParticipationFilterType extends AbstractType
{
    public function __construct(
        private SeasonService $seasonService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('season', ChoiceType::class, [
                'label' => false,
                'multiple' => false,
                'choices' => $this->seasonService->getSeasons(),
                'attr' => [
                    'class' => 'customSelect2',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une saison',
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ->add('bikeRideType', EntityType::class, [
                'label' => false,
                'class' => BikeRideType::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'customSelect2',
                    'data-width' => '100%',
                    'data-placeholder' => 'Séléctionnez un type de sortie',
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ;
    }
}
