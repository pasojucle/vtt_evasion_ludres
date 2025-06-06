<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\PracticeEnum;
use App\Form\Admin\BikeRideTypeAutocompleteField;
use App\Service\LevelService;
use App\Validator\Period;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipationFilterType extends AbstractType
{
    public function __construct(
        private LevelService $levelService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [new Period()],
            ])
            ->add('bikeRideType', BikeRideTypeAutocompleteField::class)
            ;

        if (null === $options['user']) {
            $builder
                    ->add('levels', ChoiceType::class, [
                        'label' => false,
                        'multiple' => true,
                        'choices' => $this->levelService->getLevelChoices(),
                        'required' => false,
                        'autocomplete' => true,
                        'attr' => [
                            'data-width' => '100%',
                            'data-placeholder' => 'Sélectionnez un ou plusieurs niveaux',
                        ],
                    ])
                    ->add('practice', EnumType::class, [
                        'label' => false,
                        'class' => PracticeEnum::class,
                        'autocomplete' => true,
                        'attr' => [
                            'data-width' => '100%',
                            'data-placeholder' => 'Sélectionnez une pratique',
                        ],
                        'required' => false,
                    ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
        ]);
    }
}
