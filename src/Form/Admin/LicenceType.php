<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceCoverageEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Service\SeasonService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenceType extends AbstractType
{
    public function __construct(
        private readonly SeasonService $seasonService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $licence = $event->getData();
            $form = $event->getForm();
            $currentSeason = $this->seasonService->getCurrentSeason();
            $isScholl = LicenceCategoryEnum::SCHOOL === $licence->getCategory();
 
            $notAllowedState = LicenceStateEnum::DRAFT;
            $form
                ->add('state', EnumType::class, [
                    'label' => 'État',
                    'class' => LicenceStateEnum::class,
                    'choice_filter' => ChoiceList::filter(
                        $this,
                        function (LicenceStateEnum $licenceState) use ($notAllowedState): bool {
                            return  $notAllowedState !== $licenceState;
                        },
                        $notAllowedState
                    ),
                    'autocomplete' => true,
                    'attr' => [
                        'data-width' => '100%',
                        'data-placeholder' => 'Sélectionnez un état',
                    ],
                    'required' => false,
                ])
                // ->add('isVae', ChoiceType::class, [
                //     'label' => 'Type de vélo',
                //     'choices' => [
                //         'Vélo musculaire' => false,
                //         'VTT à assistance électrique' => true,
                //     ],
                //     'row_attr' => [
                //         'class' => 'form-group-inline',
                //     ],
                // ])
                ->add('coverage', EnumType::class, [
                    'label' => 'Selectionnez une formule d\'assurance',
                    'class' => LicenceCoverageEnum::class,
                    'choice_filter' => ChoiceList::filter(
                        $this,
                        function (LicenceCoverageEnum $coverage) use ($isScholl): bool {
                            if ($isScholl) {
                                return $coverage !== LicenceCoverageEnum::MINI_GEAR;
                            }
                            return true;
                        },
                    ),
                ])
                ->add('currentSeasonForm', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'attr' => [
                        'data-switch-on' => sprintf('Assurance %s validée', $currentSeason),
                        'data-switch-off' => sprintf('Assurance %s manquante', $currentSeason),
                    ],
                    'required' => false,
                ])
                ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'season_licence' => null,
        ]);
    }
}
