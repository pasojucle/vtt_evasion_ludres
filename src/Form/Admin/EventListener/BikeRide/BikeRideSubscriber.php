<?php

declare(strict_types=1);

namespace App\Form\Admin\EventListener\BikeRide;

use App\Entity\BikeRide;

use App\Entity\BikeRideType as BikeRideKind;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\User;
use App\Form\Admin\BikeRideType;
use App\Form\Admin\UsersAutocompleteField;
use App\Form\Type\TiptapType;
use App\Repository\BikeRideTypeRepository;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\MessageService;
use App\Service\SeasonService;
use App\Validator\RangeAge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BikeRideSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BikeRideTypeRepository $bikeRideTypeRepository,
        private readonly MessageService $messageService,
        private readonly LevelService $levelService,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator
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
            $bikeRide->setRegistrationClosedMessage($this->messageService->getMessageByName('REGISTRATION_CLOSED_DEFAULT_MESSAGE'));
        }
        $this->setRestriction($bikeRide);

        $userIds = [];
        /** @var User $user */
        foreach ($bikeRide->getUsers() as $user) {
            $userIds[] = $user->getId();
        }
        $event->setData($bikeRide);

        $this->modifier($event->getForm(), $bikeRide->getBikeRideType(), $bikeRide->registrationEnabled(), $bikeRide, $bikeRide->getRestriction(), $bikeRide->getLevelFilter(), $userIds);
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $bikeRide = $event->getForm()->getData();
        $bikeRideTypeId = (array_key_exists('bikeRideType', $data)) ? (int)$data['bikeRideType'] : null;
        $registrationEnabled = (array_key_exists('registrationEnabled', $data) ? (bool) $data['registrationEnabled'] : true);
        $bikeRideType = $this->bikeRideTypeRepository->find($bikeRideTypeId);
        if ($bikeRideType && array_key_exists('handler', $data) && 'bike_ride[bikeRideType]' === $data['handler']) {
            $data['content'] = $bikeRideType->getContent();
            $data['title'] = $bikeRideType->getName();
            $data['closingDuration'] = $bikeRideType->getClosingDuration() ?? 0;
            $data['bikeRideTypeChanged'] = 0;
            $data['restriction'] = $bikeRide->getRestriction() ?? 0;
            $data['user'] = $bikeRide->getUsers();
            $data['levelFilter'] = $bikeRide->getLevelFilter();
            $data['minAge'] = $bikeRide->getMinAge();
            $data['maxAge'] = $bikeRide->getMaxAge();
            $event->setData($data);
            $bikeRide->setBikeRideType($bikeRideType);
            $event->getForm()->setData($bikeRide);
        }

        $restriction = (array_key_exists('restriction', $data)) ? (int) $data['restriction'] : null;

        $levels = (array_key_exists('levels', $data) && !empty($data['levels'])) ? explode(';', $data['levels']) : [];
        $levelFilter = array_key_exists('levelFilter', $data) ? $data['levelFilter'] : [];
        $userIds = (array_key_exists('userids', $data) && !empty($data['userids'])) ? explode(';', $data['userids']) : [];
        $this->addOrRemoveUsers($data, $levels, $userIds, $bikeRide);

        $this->cleanData($restriction, $data, $bikeRide);

        $event->setData($data);
        $event->getForm()->setData($bikeRide);

       
        $this->modifier($event->getForm(), $bikeRideType, $registrationEnabled, $bikeRide, $restriction, $levelFilter, $userIds);
    }

    private function modifier(FormInterface $form, ?BikeRideKind $bikeRideType, bool $registrationEnabled, BikeRide $bikeRide, ?int $restriction, array $levelFilter, array $userIds): void
    {
        $isDiabled = false;
        if (RegistrationEnum::NONE === $bikeRideType->getRegistration()) {
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
            ->add('content', TiptapType::class, [
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
            ])
            ;
        if ($registrationEnabled) {
            $form
                ->add('registrationClosedMessage', TiptapType::class, [
                    'label' => 'Message afficher à la cloture lors de l\'inscription',
                    'config_name' => 'base',
                    'required' => false,
                    'row_attr' => [
                        'class' => 'form-group',
                    ]
                ]);
        } else {
            $form->remove('registrationClosedMessage');
        }
        $disabled = RegistrationEnum::NONE === $bikeRideType->getRegistration();
        $disabledUsers = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_MEMBER_LIST !== $restriction;
        $disabledMinAge = ($disabled) ? $disabled : BikeRideType::RESTRICTION_TO_RANGE_AGE !== $restriction;
        $filters['season'] = SeasonService::MIN_SEASON_TO_TAKE_PART;

        $addFramersClass = 'btn btn-xs btn-primary form-modifier';
        if ($disabledUsers) {
            $addFramersClass .= ' disabled';
        }

        $form
            ->add('users', UsersAutocompleteField::class, [
                'label' => false,
                'autocomplete_url' => $this->urlGenerator->generate('admin_member_autocomplete', $filters),
                'required' => !$disabledUsers,
                'disabled' => $disabledUsers,
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
                'autocomplete' => true,
                'attr' => [
                    'data-modifier' => 'bikeRideRestriction',
                    'class' => 'form-modifier',
                    'data-width' => '100%',
                    'data-placeholder' => 'Ajouter un ou plusieurs niveaux',
                    'data-levels' => ($levelFilter) ? implode(';', $levelFilter) : '',
                    'data-add-to-fetch' => 'levels',
                ],
                'required' => false,
                'disabled' => $disabledUsers,
            ])
            ->add('minAge', IntegerType::class, [
                'label' => 'Âge minimum',
                'attr' => [
                    'min' => 5,
                    'max' => 90,
                ],
                'row_attr' => ['class' => 'form-group-inline', ],
                'required' => !$disabledMinAge,
                'disabled' => $disabledMinAge,
            ])
            ->add('maxAge', IntegerType::class, [
                'label' => 'Âge maximum',
                'attr' => [
                    'min' => 5,
                    'max' => 90,
                ],
                'row_attr' => ['class' => 'form-group-inline', ],
                'required' => false,
                'disabled' => $disabledMinAge,
                'constraints' => [new RangeAge()]
            ])
            ;
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

    private function cleanData(?int $restriction, array &$data, BikeRide $bikeRide): void
    {
        if (BikeRideType::RESTRICTION_TO_MEMBER_LIST !== $restriction) {
            $this->clearUsers($data, $bikeRide);
        }
        if (BikeRideType::RESTRICTION_TO_RANGE_AGE !== $restriction) {
            $data['minAge'] = null;
            $data['maxAge'] = null;
            $bikeRide->setMinAge(null)
                ->setMaxAge(null);
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
                return 1 === preg_match('#(\d+)#', (string) $id) ? (int) $id : $id;
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
