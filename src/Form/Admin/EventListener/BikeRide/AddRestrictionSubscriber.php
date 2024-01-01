<?php

namespace App\Form\Admin\EventListener\BikeRide;

use App\Entity\BikeRide;
use App\Entity\BikeRideType as BikeRideKind;
use App\Entity\User;
use App\Form\Admin\BikeRideType;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\SeasonService;
use App\Validator\RangeAge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class AddRestrictionSubscriber implements EventSubscriberInterface
{
    public function __construct(private LevelService $levelService, private UserRepository $userRepository)
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

        $this->modifier($event->getForm(), $bikeRide, $bikeRide->getRestriction(), $bikeRide->getLevelFilter());
    }

    public function preSubmit(FormEvent $event): void
    {
        $bikeRide = $event->getForm()->getData();

        $data = $event->getData();
        
        $restriction = (array_key_exists('restriction', $data)) ? $data['restriction'] : null;

        $levelFilter = (array_key_exists('levelFilter', $data) && !empty($data['levelFilter'])) ? $data['levelFilter'] : null;

        $levels = (array_key_exists('levels', $data) && !empty($data['levels'])) ? explode(';', $data['levels']) : null;
        if (array_key_exists('levelFilter', $data) || $levels) {
            $this->addOrRemoveUsers($data, $levels, $bikeRide);
        }

        $this->cleanData($restriction, $data, $bikeRide);

        $event->setData($data);
        $event->getForm()->setData($bikeRide);

        $this->modifier($event->getForm(), $bikeRide, $restriction, $levelFilter);
    }

    private function modifier(FormInterface $form, BikeRide $bikeRide, ?int $restriction, ?array $levelFilter): void
    {
        $disabled = BikeRideKind::REGISTRATION_NONE === $bikeRide->getBikeRideType()->getRegistration();
        $disabledUsers = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_MEMBER_LIST !== $restriction;
        $disabledMinAge = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_RANGE_AGE !== $restriction;
        $filters['season'] = SeasonService::MIN_SEASON_TO_TAKE_PART;

        $addFramersClass = 'btn btn-xs btn-primary form-modifier';
        if ($disabledUsers) {
            $addFramersClass .= ' disabled';
        }

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
                'remote_params' => [
                    'filters' => json_encode($filters),
                ],
            ])
            ->add('levelFilter', ChoiceType::class, [
                'label' => false,
                'multiple' => true,
                'choices' => $this->levelService->getLevelChoices(),
                'attr' => [
                    'data-modifier' => 'bikeRideRestriction',
                    'class' => 'customSelect2 form-modifier',
                    'data-width' => '100%',
                    'data-placeholder' => 'Ajouter un ou plusieurs niveaux',
                    'data-maximum-selection-length' => 4,
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                    'data-levels' => ($levelFilter) ? implode(';', $levelFilter) : '',
                    'data-add-to-fetch' => 'levels',
                ],
                'required' => false,
                'disabled' => $disabledUsers,
            ])
            ->add('minAge', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => 5,
                    'max' => 90,
                ],
                'required' => !$disabledMinAge,
                'disabled' => $disabledMinAge,
            ])
            ->add('maxAge', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => 5,
                    'max' => 90,
                ],
                'required' => !$disabledMinAge,
                'disabled' => $disabledMinAge,
                'constraints' => [new RangeAge()]
            ])
            ;
    }

    private function cleanData(?int $restriction, array &$data, BikeRide $bikeRide): void
    {
        if (BikeRideType::RESTRICTION_TO_MEMBER_LIST !== $restriction) {
            $data['users'] = [];
            $bikeRide->clearUsers();
        }
        if (BikeRideType::RESTRICTION_TO_RANGE_AGE !== $restriction) {
            $data['minAge'] = '';
        }
        if (array_key_exists('users', $data)) {
            foreach ($data['users'] as $user) {
                if (empty($user)) {
                    unset($user);
                }
            }
        }
    }

    private function addOrRemoveUsers(array &$data, ?array $levels, BikeRide $bikeRide): void
    {
        $levelFilter = (array_key_exists('levelFilter', $data)) ? $data['levelFilter'] : null;
        if (!$levelFilter && $levels) {
            $this->clearUsers($data, $bikeRide);
            return;
        }

        $levelsToAdd = ($levelFilter) ? $levelFilter : [];
        
        $levelsToRemove = ($levels) ? $levels : [];
        if ($levelFilter && $levels) {
            $levelsToAdd = array_diff($levelFilter, $levels);
            $levelsToRemove = array_diff($levels, $levelFilter);
        }
        
        $userToAdd = $this->getUsers($levelsToAdd);
        $usersToRemove = $this->getUsers($levelsToRemove);
        if (!array_key_exists('users', $data)) {
            $data['users'] = [];
        }

        foreach ($userToAdd as $user) {
            if (!in_array($user->getId(), $data['users'])) {
                $data['users'][] = $user->getId();
            }
        }

        foreach ($usersToRemove as $users) {
            $key = array_search($users->getId(), $data['users']);
            if ($key) {
                unset($data['users'][$key]);
                $bikeRide->removeUser($users);
            }
        }
    }

    private function getUsers(array $levels): array
    {
        if (!empty($levels)) {
            $filters = [
                'fullName' => null,
                'user' => null,
                'levels' => $levels,
                'season' => SeasonService::MIN_SEASON_TO_TAKE_PART,
            ];
            return $this->userRepository->findMemberQuery($filters)->getQuery()->getResult();
        }
        return [];
    }

    private function clearUsers(array &$data, BikeRide $bikeRide): void
    {
        $data['users'] = [];
        $bikeRide->clearUsers();
        $bikeRide->setLevelFilter([]);
    }
}
