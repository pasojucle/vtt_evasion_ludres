<?php

namespace App\Form\Admin\EventListener\Survey;

use App\Entity\BikeRide;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\Admin\SurveyType;
use App\Form\Transformer\BikeRideTransformer;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\SeasonService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $levelFilter = (array_key_exists('levelFilter', $data) && !empty($data['levelFilter'])) ? $data['levelFilter'] : [];

        $levels = (array_key_exists('levels', $data) && !empty($data['levels'])) ? explode(';', $data['levels']) : [];
        $memberIds = (array_key_exists('memberIds', $data) && !empty($data['memberIds'])) ? explode(';', $data['memberIds']) : [];
        $this->addOrRemoveMembers($data, $levels, $memberIds, $survey);

        $this->cleanData($restriction, $data, $survey);
        $event->setData($data);
        $event->getForm()->setData($survey);

        $this->modifier($event->getForm(), $restriction, $levelFilter, $memberIds);
    }

    private function modifier(FormInterface $form, ?int $restriction, ?array $levelFilter, ?array $memberIds): void
    {
        $options = $form->getConfig()->getOptions();
        $disabledMembers = SurveyType::DISPLAY_MEMBER_LIST !== $restriction;
        $disabledBikeRide = SurveyType::DISPLAY_BIKE_RIDE !== $restriction;

        $form
            ->add('bikeRide', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'admin_bike_ride_choices',
                'class' => BikeRide::class,
                'primary_key' => 'id',
                'transformer' => BikeRideTransformer::class,
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000,
                'language' => 'fr',
                'placeholder' => 'Sélectionnez une sortie',
                'width' => '100%',
                'label' => false,
                'required' => !$disabledBikeRide,
                'disabled' => $disabledBikeRide,
            ])
            ->add('members', Select2EntityType::class, [
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
                'placeholder' => 'Sélectionnez les adhérents',
                'width' => '100%',
                'label' => false,
                'remote_params' => [
                    'filters' => json_encode($options['filters']),
                ],
                'required' => !$disabledMembers,
                'disabled' => $disabledMembers,
                'attr' => [
                    'data-modifier' => 'bikeRideRestriction',
                    'class' => 'form-modifier',
                    'data-memberIds' => ($memberIds) ? implode(';', $memberIds) : '',
                    'data-add-to-fetch' => 'memberIds',
                ],
            ])
            ->add('levelFilter', ChoiceType::class, [
                'label' => false,
                'multiple' => true,
                'choices' => $this->levelService->getLevelChoices(),
                'attr' => [
                    'data-modifier' => 'surveyRestriction',
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
                'disabled' => $disabledMembers,
            ])
            ;
    }

    private function addOrRemoveMembers(array &$data, ?array $levels, ?array $memberIds, Survey $survey): void
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
        if (!array_key_exists('members', $data)) {
            $data['members'] = [];
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
        if (array_key_exists('members', $data)) {
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
