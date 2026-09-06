<?php

declare(strict_types=1);

namespace App\Form\Admin\EventListener;

use App\Entity\Cluster;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Enum\RegistrationEnum;
use App\Form\Admin\UserAutocompleteField;
use App\Form\HiddenEntityType;
use App\Form\SurveyResponsesType;
use App\Service\SessionService;
use App\Validator\SessionUniqueMember;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddSessionSubscriber implements EventSubscriberInterface
{
    private const array ALLOWED_PRACTICES = [PracticeEnum::VTT, PracticeEnum::GRAVEL];

    public function __construct(
        private SessionService $sessionService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        dump($data);

        $bikeRide = $data->cluster->getBikeRide();
        if (RegistrationEnum::CLUSTERS === $bikeRide->getBikeRideType()->getRegistration() && 1 < $this->sessionService->selectableClusterCount($bikeRide, $bikeRide->getClusters())) {
            $form
                ->add('cluster', EntityType::class, [
                    'label' => 'Selectionnez le groupe',
                    'class' => Cluster::class,
                    'choices' => $bikeRide->getClusters(),
                    'expanded' => true,
                    'multiple' => false,
                    'block_prefix' => 'checkgroup',
                ])
            ;
        } else {
            $form->add('cluster', HiddenEntityType::class, [
                        'class' => Cluster::class,
                    ]);
            ;
        }
        if (true === $bikeRide->getBikeRideType()->isDisplayBikeKind()) {
            $form
                ->add('practice', EnumType::class, [
                    'label' => 'Activité',
                    'class' => PracticeEnum::class,
                    'choices' => [PracticeEnum::VTT, PracticeEnum::GRAVEL, PracticeEnum::WALKING],
                    'expanded' => true,
                    'multiple' => false,
                    'block_prefix' => 'checkgroup',
                    'choice_attr' => function ($choice, string $key, mixed $value) {
                        return [
                            'data-action' => 'change->form-modifier#change',
                            'data-container-id' => 'session-bike-type'
                        ];
                    },
                ]);
        }

        if ($data->surveyResponses) {
            $form
                ->add('surveyResponses', SurveyResponsesType::class, [
                    'label' => false,
                ]);
        }
    
        
        $this->modifier($form, $data->season, $data->level, PracticeEnum::NONE);
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $season = $data['season'] ?? null;
        $level = $data['level'] ?? null;
        $practice = array_key_exists('practice', $data) ? PracticeEnum::tryFrom($data['practice']) : PracticeEnum::NONE;

        $this->modifier($event->getForm(), $season, $level, $practice);
    }

    private function modifier(FormInterface $form, null|int|string $season, null|int|string $level, ?PracticeEnum $practice): void
    {
        $form
            ->add('user', UserAutocompleteField::class, [
                'label' => 'Participant',
                'autocomplete_url' => $this->urlGenerator->generate('admin_member_autocomplete', [
                    'season'=> $season,
                    'levels' => [$level],
                ]),
                'constraints' => [
                    new NotBlank(),
                    new SessionUniqueMember(),
                ],
                'required' => true,
            ]);

        [$choices, $hidden, $required] = (in_array($practice, self::ALLOWED_PRACTICES))
            ? [[BikeTypeEnum::MUSCULAR, BikeTypeEnum::ELECTRIC, ], '', true]
            : [[BikeTypeEnum::NONE], 'hidden', false];
        $form
            ->add('bikeType', EnumType::class, [
                'label' => 'Type de vélo',
                'class' => BikeTypeEnum::class,
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'checkgroup',
                'required' => $required,
                'row_attr' => [
                    'class' => $hidden
                ]
            ]);
    }
}
