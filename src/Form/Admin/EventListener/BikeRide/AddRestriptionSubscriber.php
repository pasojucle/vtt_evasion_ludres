<?php

namespace App\Form\Admin\EventListener\BikeRide;

use App\Entity\BikeRide;
use App\Entity\User;
use App\Form\Admin\BikeRideType;
use App\Service\LevelService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class AddRestriptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private LevelService $levelService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $bikeRide = $event->getData();

        $this->modifier($event->getForm(), $bikeRide, $bikeRide->getRestriction());
    }

    public function preSubmit(FormEvent $event): void
    {
        $bikeRide = $event->getForm()->getData();

        $data = $event->getData();

        $restriction = (array_key_exists('restriction', $data)) ? $data['restriction'] : null;

        $this->cleanData($restriction, $data, $bikeRide);
        $event->setData($data);
        $event->getForm()->setData($bikeRide);

        $this->modifier($event->getForm(), $bikeRide, $restriction);
    }

    private function modifier(
        FormInterface $form,
        $bikeRide,
        ?int $restriction
    ): void {
        $disabled = !$bikeRide->getBikeRideType()->isRegistrable();
        $disabledUsers = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_MEMBER_LIST !== $restriction;
        $disabledLevelFilter = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_LEVELS !== $restriction;
        $disabledMinAge = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_MIN_AGE !== $restriction;

        $form
            ->add('users', Select2EntityType::class, [
                'multiple' => true,
                'remote_route' => 'admin_member_choices',
                'class' => User::class,
                'primary_key' => 'id',
                'text_property' => 'fullName',
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000,
                'language' => 'fr',
                'placeholder' => 'Saisissez un nom et prénom',
                'width' => '100%',
                'label' => false,
                'required' => !$disabledUsers,
                'disabled' => $disabledUsers,
            ])
            ->add('levelFilter', ChoiceType::class, [
                'label' => false,
                'multiple' => true,
                'choices' => $this->levelService->getLevelChoices(),
                'attr' => [
                    'class' => 'customSelect2',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez un ou plusieurs niveaux',
                    'data-maximum-selection-length' => 4,
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => !$disabledLevelFilter,
                'disabled' => $disabledLevelFilter,
            ])
            ->add('minAge', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => 10,
                    'max' => 90,
                ],
                'required' => !$disabledMinAge,
                'disabled' => $disabledMinAge,
            ])
            ;
    }

    private function cleanData(?int $restriction, array &$data, BikeRide $bikeRide): void
    {
        if (BikeRideType::RESTRICTION_TO_MEMBER_LIST !== $restriction) {
            $data['users'] = [];
            $bikeRide->clearUsers();
        }
        if (BikeRideType::RESTRICTION_TO_LEVELS !== $restriction) {
            $data['levelFilter'] = [];
            $bikeRide->clearLevels();
            $bikeRide->setLevelTypes([]);
        }
        if (BikeRideType::RESTRICTION_TO_MIN_AGE !== $restriction) {
            $data['minAge'] = '';
        }
    }
}
