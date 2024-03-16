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
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $bikeRide = $event->getData();

        $userIds = [];
        /** @var User $user */
        foreach ($bikeRide->getUsers() as $user) {
            $userIds[] = $user->getId();
        }

        $this->modifier($event->getForm(), $bikeRide, $bikeRide->getRestriction(), $bikeRide->getLevelFilter(), $userIds);
    }

    public function preSubmit(FormEvent $event): void
    {
        $bikeRide = $event->getForm()->getData();

        $data = $event->getData();
        
        $restriction = (array_key_exists('restriction', $data)) ? $data['restriction'] : null;

        $levels = (array_key_exists('levels', $data) && !empty($data['levels'])) ? explode(';', $data['levels']) : [];
        $levelFilter = array_key_exists('levelFilter', $data) ? $data['levelFilter'] : [];
        $userIds = (array_key_exists('userids', $data) && !empty($data['userids'])) ? explode(';', $data['userids']) : [];
        $this->addOrRemoveUsers($data, $levels, $userIds, $bikeRide);

        $this->cleanData($restriction, $data, $bikeRide);

        $event->setData($data);
        $event->getForm()->setData($bikeRide);

        $this->modifier($event->getForm(), $bikeRide, $restriction, $levelFilter, $userIds);
    }

    private function modifier(FormInterface $form, BikeRide $bikeRide, ?int $restriction, array $levelFilter, array $userIds): void
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
                'attr' => [
                    'data-modifier' => 'bikeRideRestriction',
                    'class' => 'form-modifier',
                    'data-userids' => ($userIds) ? implode(';', $userIds) : '',
                    'data-add-to-fetch' => 'userids',
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
            $this->clearUsers($data, $bikeRide);
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

    private function addOrRemoveUsers(array &$data, ?array $levels, ?array $userIds, BikeRide $bikeRide): void
    {
        $levelFilter = [];
        if (array_key_exists('levelFilter', $data)) {
            $levelFilter = array_map(function ($id) {
                return (int) $id;
            }, $data['levelFilter']);
        }
        $users = [];
        if (array_key_exists('users', $data)) {
            $users = array_map(function ($id) {
                return (int) $id;
            }, $data['users']);
        }
        if (!array_key_exists('handler', $data)) {
            return;
        }

        $usersToAdd = [];
        $usersToRemove = [];
        if (str_contains($data['handler'], 'levelFilter')) {
            $levelsToAdd = array_diff($levelFilter, $levels);
            $levelsToRemove = array_diff($levels, $levelFilter);
            $usersToAdd = $this->getUsers($levelsToAdd);
            $usersToRemove = $this->getUsers($levelsToRemove);
        }
        if (str_contains($data['handler'], 'userids')) {
            $usersToAdd = array_diff($userIds, $users);
            $usersToRemove = array_diff($users, $userIds);
        }
        if (!array_key_exists('users', $data)) {
            $data['users'] = [];
        }
        foreach ($usersToAdd as $user) {
            $id = ($user instanceof User) ? $user->getId() : $user;
            if (!in_array($id, $data['users'])) {
                $data['users'][] = $id;
            }
        }
        foreach ($usersToRemove as $user) {
            $id = ($user instanceof User) ? $user->getId() : $user;
            $key = array_search($id, $data['users']);
            if (null !== $key) {
                unset($data['users'][$key]);
                $user = ($user instanceof User) ? $user : $this->userRepository->find($id);
                $bikeRide->removeUser($user);
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
        unset($data['levelsFilter']);
        $bikeRide->clearUsers()
            ->setLevelFilter([]);
    }
}
