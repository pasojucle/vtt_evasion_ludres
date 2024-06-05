<?php

namespace App\Form\Admin\EventListener\Survey;

use App\Entity\Survey;
use App\Entity\User;
use App\Form\Admin\BikeRideAutocompleteField;
use App\Form\Admin\SurveyType;
use App\Form\Admin\UsersAutocompleteField;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\SeasonService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddRestrictionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LevelService $levelService,
        private readonly UserRepository $userRepository,
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

        $this->modifier($event->getForm(), $survey->getRestriction(), $survey->getLevelFilter(), $memberIds);
    }

    public function preSubmit(FormEvent $event): void
    {
        $survey = $event->getForm()->getData();

        $data = $event->getData();
        $restriction = (array_key_exists('restriction', $data)) ? $data['restriction'] : null;
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
        $event->setData($data);
        $event->getForm()->setData($survey);

        $this->modifier($event->getForm(), $restriction, $levelFilter, $memberIds);
    }

    private function modifier(FormInterface $form, ?int $restriction, ?array $levelFilter, ?array $memberIds): void
    {
        $options = $form->getConfig()->getOptions();
        $disabledMembers = SurveyType::DISPLAY_MEMBER_LIST !== $restriction || $options['display_disabled'];
        $disabledBikeRide = SurveyType::DISPLAY_BIKE_RIDE !== $restriction || $options['display_disabled'];

        if (!$disabledBikeRide) {
            $form
            ->add('bikeRide', BikeRideAutocompleteField::class, [
                'required' => true,
                'disabled' => $disabledBikeRide,
            ]);
        }
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
}
