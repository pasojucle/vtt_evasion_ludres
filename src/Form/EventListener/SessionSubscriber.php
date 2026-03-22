<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Form\HiddenEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SessionSubscriber implements EventSubscriberInterface
{
    private const array ALLOWED_PRACTICES = [PracticeEnum::VTT, PracticeEnum::GRAVEL];


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
        $session = $event->getData();
        $options = $form->getConfig()->getOptions();

        if (true === $options['is_writable_availability']) {
            $notAllowedAvailabilities = [AvailabilityEnum::NONE];
            if (!$session->getCluster()->getBikeRide()->getBikeRideType()->isNeedFramers()) {
                $notAllowedAvailabilities[] = AvailabilityEnum::AVAILABLE;
            }

            $form
                ->add('availability', EnumType::class, [
                    'label' => false,
                    'class' => AvailabilityEnum::class,
                    'choices' => [AvailabilityEnum::REGISTERED, AvailabilityEnum::AVAILABLE, AvailabilityEnum::UNAVAILABLE],
                    'expanded' => true,
                    'multiple' => false,
                    'block_prefix' => 'customcheck',
                ]);
        } else {
            if (null === $session->getCluster()) {
                $form
                    ->add('cluster', EntityType::class, [
                        'label' => 'Choisiez votre groupe',
                        'class' => Cluster::class,
                        'choices' => $options['clusters'],
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'checkgroup',
                    ]);
            } else {
                $form
                    ->add('cluster', HiddenEntityType::class, [
                        'class' => Cluster::class,
                    ]);
            }
        }
        
        if (true === $options['display_bike_kind']) {
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
    
        $this->modifier($form, $session->getPractice());
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $practice= array_key_exists('practice', $data) ? PracticeEnum::tryFrom($data['practice']) : PracticeEnum::NONE;

        $this->modifier($event->getForm(), $practice);
    }

    private function modifier(FormInterface $form,  ?PracticeEnum $practice): void
    {
        $displayBikeKind = $form->getConfig()->getOption('display_bike_kind');
        [$choices, $hidden] = ($displayBikeKind && in_array($practice, self::ALLOWED_PRACTICES))
            ? [[BikeTypeEnum::MUSCULAR, BikeTypeEnum::ELECTRIC,], '']
            : [[BikeTypeEnum::NONE], 'hidden'];
        $form
            ->add('bikeType', EnumType::class, [
                'label' => 'Type de vélo',
                'class' => BikeTypeEnum::class,
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'checkgroup',
                'row_attr' => [
                    'class' => $hidden
                ]
            ]);
    }
}
