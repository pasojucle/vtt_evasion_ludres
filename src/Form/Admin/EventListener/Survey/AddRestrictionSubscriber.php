<?php

declare(strict_types=1);

namespace App\Form\Admin\EventListener\Survey;

use App\Entity\BikeRide;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\Admin\BikeRideAutocompleteField;
use App\Form\Admin\SurveyType;
use App\Form\Admin\UsersAutocompleteField;
use App\Form\HiddenArrayType;
use App\Repository\BikeRideRepository;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\SeasonService;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddRestrictionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LevelService $levelService,
        private readonly UserRepository $userRepository,
        private readonly BikeRideRepository $bikeRideRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
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
        $survey = $event->getData();

        $memberIds = [];
        /** @var User $member */
        foreach ($survey->getMembers() as $member) {
            $memberIds[] = $member->getId();
        }

        $this->modifier($event->getForm(), $survey->getRestriction(), $survey->getLevelFilter(), $memberIds, $survey->getBikeRide());
    }

    public function preSubmit(FormEvent $event): void
    {
        $survey = $event->getForm()->getData();

        $data = $event->getData();
        $restriction = (array_key_exists('restriction', $data)) ? (int) $data['restriction'] : null;
        $levelFilter = null;
        $levels = $this->toArray($data, 'levels');
        $memberIds = $this->toArray($data, 'memberIds');
        if ($restriction && array_key_exists('handler', $data)) {
            foreach (['levelFilter', 'members'] as $property) {
                $this->initProperty($data, $property);
            }
            $levelFilter = $data['levelFilter'];
            $this->addOrRemoveMembers($data, $levels, $memberIds, $survey);
            $this->cleanData($restriction, $data, $survey);
        }
        
        $this->setPeriod($restriction, $survey, $data);
        $event->setData($data);
        $event->getForm()->setData($survey);
        $this->modifier($event->getForm(), $restriction, $levelFilter, $memberIds, $survey->getBikeRide());
    }

    private function modifier(FormInterface $form, ?int $restriction, ?array $levelFilter, ?array $memberIds, ?BikeRide $bikeRide): void
    {
        $options = $form->getConfig()->getOptions();
        $disabledMembers = SurveyType::DISPLAY_MEMBER_LIST !== $restriction;
        $disabledBikeRide = SurveyType::DISPLAY_BIKE_RIDE !== $restriction || $options['display_disabled'];
        $disabledPeriod = SurveyType::DISPLAY_BIKE_RIDE === $restriction && null !== $bikeRide;
        if (!$disabledBikeRide) {
            $form
                ->add('bikeRide', BikeRideAutocompleteField::class, [
                    'autocomplete_url' => $this->urlGenerator->generate('admin_bike_ride_autocomplete'),
                    'required' => true,
                    'disabled' => $disabledBikeRide,
                    'attr' => [
                        'data-modifier' => 'surveyRestriction',
                        'class' => 'form-modifier',
                    ],
                ]);
        } else {
            $form->add('bikeRide', HiddenType::class);
        }
        $form
            ->add('startAt', DateTimeType::class, [
                    'label' => 'Date de début',
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
                    'disabled' => $disabledPeriod,
                ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'Date de fin',
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
                'disabled' => $disabledPeriod,
            ]);
        if (!$disabledMembers) {
            $form
                ->add('members', UsersAutocompleteField::class, [
                    'autocomplete_url' => $this->urlGenerator->generate('admin_member_autocomplete', $options['filters']),
                    'required' => true,
                    'disabled' => $disabledMembers,
                    'attr' => [
                        'data-modifier' => 'surveyRestriction',
                        'class' => 'form-modifier',
                        'data-memberIds' => ($memberIds) ? implode(';', $memberIds) : '',
                        'data-add-to-fetch' => 'memberIds',
                    ],
                ])
                ->add('levelFilter', ChoiceType::class, [
                    'label' => false,
                    'multiple' => true,
                    'choices' => $this->levelService->getLevelChoices(),
                    'autocomplete' => true,
                    'attr' => [
                        'data-modifier' => 'surveyRestriction',
                        'class' => 'form-modifier',
                        'data-width' => '100%',
                        'data-placeholder' => 'Ajouter un ou plusieurs niveaux',
                        'data-levels' => ($levelFilter) ? implode(';', $levelFilter) : '',
                        'data-add-to-fetch' => 'levels',
                    ],
                    'required' => false,
                    'disabled' => $disabledMembers,
                ])
            ;
        } else {
            $form
                ->add('members', HiddenArrayType::class, ['data' => []])
                ->add('levelFilter', HiddenArrayType::class, ['data' => []]);
        }
    }

    private function addOrRemoveMembers(array &$data, ?array $levels, ?array $memberIds, Survey $survey): void
    {
        $levelFilter = [];
        $levelFilter = array_map(function ($id) {
            return 1 === preg_match('#(\d+)#', $id) ? (int) $id : $id;
        }, $data['levelFilter']);

        $users = [];
        $users = array_map(function ($id) {
            return (int) $id;
        }, $data['members']);

        $membersToAdd = [];
        $membresToRemove = [];

        if (str_contains($data['handler'], 'levelFilter')) {
            $levelsToAdd = array_diff($levelFilter, $levels);
            $levelsToRemove = array_diff($levels, $levelFilter);
            $membersToAdd = $this->getMembers($levelsToAdd);
            $membresToRemove = $this->getMembers($levelsToRemove);
        }
        if (str_contains($data['handler'], 'memberIds')) {
            $membersToAdd = array_diff($memberIds, $users);
            $membresToRemove = array_diff($users, $memberIds);
        }

        foreach ($membersToAdd as $member) {
            $id = ($member instanceof User) ? $member->getId() : $member;
            if (!in_array($id, $data['members'])) {
                $data['members'][] = $id;
            }
        }

        foreach ($membresToRemove as $member) {
            $id = ($member instanceof User) ? $member->getId() : $member;
            $key = array_search($id, $data['members']);
            if (null !== $key) {
                unset($data['members'][$key]);
                $member = ($member instanceof User) ? $member : $this->userRepository->find($id);
                $survey->removeMember($member);
            }
        }
    }

    private function initProperty(array &$data, string $property): void
    {
        if (!array_key_exists($property, $data) || !is_array($data[$property])) {
            $data[$property] = [];
        }
    }

    private function toArray(array $data, string $property): array
    {
        return (!empty($data[$property]) && !empty($data[$property])) ? explode(';', $data[$property]) : [];
    }

    private function getMembers(array $levels): array
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

    private function cleanData(?int $restriction, array &$data, Survey $survey): void
    {
        if (SurveyType::DISPLAY_MEMBER_LIST !== $restriction) {
            $this->clearMembers($data, $survey);
        }
        if (SurveyType::DISPLAY_BIKE_RIDE !== $restriction) {
            $data['bikeRide'] = [];
            $survey->setBikeRide(null);
        }
        if (array_key_exists('members', $data) && is_array($data['members'])) {
            foreach ($data['members'] as $member) {
                if (empty($member)) {
                    unset($member);
                }
            }
        }
    }

    private function clearMembers(array &$data, Survey $survey): void
    {
        $data['members'] = [];
        unset($data['levelsFilter']);
        $survey->clearMembers()
            ->setLevelFilter([]);
    }

    private function setPeriod(int $restriction, Survey $survey, array &$data): void
    {
        $startAt = (array_key_exists('startAt', $data)) ? DateTimeImmutable::createFromFormat('d/m/Y',$data['startAt']) : new DateTimeImmutable();
        $endAt = (array_key_exists('endAt', $data)) ? DateTimeImmutable::createFromFormat('d/m/Y',$data['endAt']) : new DateTimeImmutable();

        if (SurveyType::DISPLAY_BIKE_RIDE === $restriction && array_key_exists('bikeRide', $data)) {
            $bikeRide = $this->bikeRideRepository->find($data['bikeRide']);
            if ($bikeRide) {
                $intervalDisplay = new DateInterval('P' . $bikeRide->GetDisplayDuration() . 'D');
                $startAt = $bikeRide->getStartAt()->sub($intervalDisplay);
                $endAt = $bikeRide->getStartAt();
                $survey->setBikeRide($bikeRide);
            }
        } else {
            $data['bikeRide'] = null;
            $survey->setBikeRide(null);
        }
        $startAt = $startAt->setTime(0, 0, 0);
        $endAt = $endAt->setTime(23, 59, 59);
        $survey->setStartAt($startAt);
        $survey->setEndAt($endAt);
        $data['startAt'] = $startAt->format('d/m/Y');
        $data['endAt'] = $endAt->format('d/m/Y');
    }
}
