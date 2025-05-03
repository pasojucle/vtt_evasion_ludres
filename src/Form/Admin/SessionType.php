<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Cluster;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Licence;
use App\Form\HiddenClusterType;
use App\Form\SurveyResponsesType;
use App\Service\SeasonService;
use App\Service\SessionService;
use App\Validator\SessionUniqueMember;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SessionType extends AbstractType
{
    public function __construct(
        private readonly SeasonService $seasonService,
        private readonly SessionService $sessionService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('season', ChoiceType::class, [
                'label' => false,
                'multiple' => false,
                'choices' => $this->getSeasonChoices(),
                'autocomplete' => true,
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'admin_session_add',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une saison',
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ;

        $formModifier = function (FormInterface $form, null|int|string $season) use ($options) {
            $filters = $options['filters'];
            $filters['season'] = $season;
            $form
                ->add('user', UserAutocompleteField::class, [
                    'autocomplete_url' => $this->urlGenerator->generate('admin_member_autocomplete', $filters),
                    'constraints' => [
                        new NotBlank(),
                        new SessionUniqueMember(),
                    ],
                    'required' => true,
                ]);
        };
    
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier, $options) {
            $form = $event->getForm();
            $data = $event->getData();

            $bikeRide = $options['bikeRide'];
            if (RegistrationEnum::CLUSTERS === $bikeRide->getBikeRideType()->getRegistration() && 1 < $this->sessionService->selectableClusterCount($bikeRide, $bikeRide->getClusters())) {
                $form
                        ->add('cluster', EntityType::class, [
                            'label' => false,
                            'class' => Cluster::class,
                            'choices' => $bikeRide->getClusters(),
                            'expanded' => true,
                            'multiple' => false,
                            'block_prefix' => 'customcheck',
                        ])
                    ;
            } else {
                $form
                        ->add('cluster', HiddenClusterType::class)
                    ;
            }

            if (array_key_exists('responses', $data)) {
                $form
                    ->add('responses', SurveyResponsesType::class, [
                        'label' => false,
                    ]);
            }

            $formModifier($form, $data['season']);
        });
    
        $builder->get('season')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $season = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $season, );
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => null,
            'bikeRide' => null,
        ]);
    }

    private function getSeasonChoices(): array
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $seasonChoices = ['Saison ' . $currentSeason => 'SEASON_' . $currentSeason];
        $minSeasonToTakePart = $this->seasonService->getMinSeasonToTakePart();
        if ($minSeasonToTakePart < $currentSeason) {
            $seasonChoices = ['Saison ' . $minSeasonToTakePart => 'SEASON_' . $minSeasonToTakePart];
        }
        
        $seasonChoices['licence.status.testing_in_processing'] = Licence::STATUS_TESTING_IN_PROGRESS;
        $seasonChoices['licence.status.in_processing'] = Licence::STATUS_IN_PROCESSING;

        return $seasonChoices;
    }
}
