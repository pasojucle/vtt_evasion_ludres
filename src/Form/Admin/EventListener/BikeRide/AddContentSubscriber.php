<?php

namespace App\Form\Admin\EventListener\BikeRide;

use App\Entity\BikeRide;

use App\Form\Type\CkeditorType;
use App\Form\Admin\BikeRideType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use App\Repository\BikeRideTypeRepository;
use App\Entity\BikeRideType as BikeRideKind;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddContentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BikeRideTypeRepository $bikeRideTypeRepository,
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
        $bikeRide = $event->getData();

        if (null === $bikeRide) {
            $bikeRide = new BikeRide();
            $bikeRide->setBikeRideType($this->bikeRideTypeRepository->findDefault());
        }

        $this->setRestriction($bikeRide);
        $event->setData($bikeRide);

        $this->modifier($event->getForm(), $bikeRide->getBikeRideType(), $bikeRide->registrationEnabled());
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $bikeRide = $event->getForm()->getData();
        $bikeRideTypeId = (array_key_exists('bikeRideType', $data)) ? (int)$data['bikeRideType'] : null;
        $registrationEnabled = (array_key_exists('registrationEnabled', $data) ? $data['registrationEnabled'] : true);
        $bikeRideType = $this->bikeRideTypeRepository->find($bikeRideTypeId);

        if ($bikeRideType && array_key_exists('bikeRideTypeChanged', $data) && 1 === (int)$data['bikeRideTypeChanged']) {
            $data['content'] = $bikeRideType->getContent();
            $data['title'] = $bikeRideType->getName();
            $data['closingDuration'] = $bikeRideType->getClosingDuration() ?? 0;
            $data['bikeRideTypeChanged'] = 0;
            $event->setData($data);
            $bikeRide->setBikeRideType($bikeRideType);
            $event->getForm()->setData($bikeRide);
        }
       
        $this->modifier($event->getForm(), $bikeRideType, $registrationEnabled);
    }

    private function modifier(FormInterface $form, ?BikeRideKind $bikeRideType, bool $registrationEnabled): void
    {
        $isDiabled = false;
        if (BikeRideKind::REGISTRATION_NONE === (int) $bikeRideType->getRegistration()) {
            $registrationEnabled = false;
            $isDiabled = true;
        }
        $form
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                    'empty_data' => BikeRide::DEFAULT_TITLE,
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ])
                ->add('content', CkeditorType::class, [
                    'label' => 'Détail (optionnel)',
                    'config_name' => 'full',
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
                        'Imposer une tranche d\'âge' => BikeRideType::RESTRICTION_TO_RANGE_AGE,
                    ],
                    'choice_attr' => function () {
                        return [
                            'data-modifier' => 'bikeRideRestriction',
                            'class' => 'form-modifier',
                         ];
                    },
                    'disabled' => $isDiabled,
                ])
                ->add('registrationEnabled', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'required' => false,
                    'data' => $registrationEnabled,
                    'disabled' => $isDiabled,
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'attr' => [
                        'data-switch-on' => 'Les inscriptions et desinscriptions sont activées',
                        'data-switch-off' => 'Les inscriptions et desinscriptions sont bloquées',
                    ],
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
            !$bikeRide->getUsers()->isEmpty() || !empty($bikeRide->getLevelFilter()) => BikeRideType::RESTRICTION_TO_MEMBER_LIST,
            null !== $bikeRide->getMinAge() => BikeRideType::RESTRICTION_TO_RANGE_AGE,
            default => BikeRideType::NO_RESTRICTION,
        };

        $bikeRide->setRestriction($restriction);
    }
}
