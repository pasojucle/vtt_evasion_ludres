<?php

namespace App\Form\Admin\EventListener\BikeRide;

use App\Entity\BikeRide;

use App\Entity\BikeRideType as BikeRideKind;
use App\Entity\Level;
use App\Form\Admin\BikeRideType;
use App\Repository\BikeRideTypeRepository;
use App\Service\LevelService;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class AddContentSubscriber implements EventSubscriberInterface
{
    public function __construct(private BikeRideTypeRepository $bikeRideTypeRepository)
    {
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
        $bikeRide = $event->getData();

        $this->setRestriction($bikeRide);
        $this->setLevelFilter($bikeRide);
        $event->setData($bikeRide);

        $this->modifier($event->getForm(), $bikeRide->getBikeRideType());
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $bikeRideTypeId = (array_key_exists('bikeRideType', $data)) ? (int)$data['bikeRideType'] : null;
        $bikeRide = $event->getForm()->getData();

        $bikeRideType = $this->bikeRideTypeRepository->find($bikeRideTypeId);
        
        if ($bikeRide->getBikeRideType()->getId() !== $bikeRideTypeId) {
            $data['content'] = $bikeRideType->getContent();
            $data['title'] = $bikeRideType->getName();
        }
        $event->setData($data);
       
        $this->modifier($event->getForm(), $bikeRideType);
    }

    private function modifier(FormInterface $form, ?BikeRideKind $bikeRideType): void
    {
        $isDiabled = BikeRideKind::REGISTRATION_NONE === $bikeRideType?->getRegistration();
            
        $form
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                    'empty_data' => BikeRide::DEFAULT_TITLE,
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ])
                ->add('content', CKEditorType::class, [
                    'label' => 'Détail (optionnel)',
                    'config_name' => 'minimum_config',
                    'required' => false,
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ])
                ->add('endAt', DateTimeType::class, [
                    'input' => 'datetime_immutable',
                    'label' => 'Date de fin (optionnel)',
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
                    'required' => false,
                    'disabled' => $isDiabled,
                ])
                ->add('closingDuration', IntegerType::class, [
                    'label' => 'Fin d\'inscription (nbr de jours avant)',
                    'required' => false,
                    'attr' => [
                        'min' => 0,
                        'max' => 90,
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'disabled' => $isDiabled,
                ])
                ->add('restriction', ChoiceType::class, [
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => [
                        'Accessible à tous les membres' => BikeRideType::NO_RESTRICTION,
                        'Limiter à des participants' => BikeRideType::RESTRICTION_TO_MEMBER_LIST,
                        'Limiter à un groupe de niveau' => BikeRideType::RESTRICTION_TO_LEVELS,
                        'Imposer un âge minimum' => BikeRideType::RESTRICTION_TO_MIN_AGE,
                    ],
                    'choice_attr' => function () {
                        return [
                            'data-modifier' => 'bike_ride_Restriction',
                            'class' => 'form-modifier',
                         ];
                    },
                    'disabled' => $isDiabled,
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Enregistrer',
                    'attr' => [
                        'class' => 'btn btn-primary float-right',
                    ],
                ]);
    }

    private function setRestriction(BikeRide &$bikeRide): void
    {
        $restriction = match (true) {
            !$bikeRide->getUsers()->isEmpty() => BikeRideType::RESTRICTION_TO_MEMBER_LIST,
            !$bikeRide->getLevels()->isEmpty() || !empty($bikeRide->getLevelTypes()) => BikeRideType::RESTRICTION_TO_LEVELS,
            null !== $bikeRide->getMinAge() => BikeRideType::RESTRICTION_TO_MIN_AGE,
            default => BikeRideType::NO_RESTRICTION,
        };

        $bikeRide->setRestriction($restriction);
    }

    private function setLevelFilter(BikeRide &$bikeRide): void
    {
        $levelFilter = [];
        foreach ($bikeRide->getLevels() as $level) {
            $levelFilter[] = $level->getId();
        }
        foreach ($bikeRide->getLevelTypes() as $levelType) {
            $levelTypeFilter = match ($levelType) {
                Level::TYPE_SCHOOL_MEMBER => Level::TYPE_ALL_MEMBER,
                Level::TYPE_FRAME => Level::TYPE_ALL_FRAME,
                default => $levelType
            };
            $levelFilter[] = $levelTypeFilter;
        }

        $bikeRide->setLevelFilter($levelFilter);
    }
}
