<?php

namespace App\Form\Admin\EventListener\Survey;

use App\Entity\BikeRide;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\Admin\BikeRideType;
use App\Form\Admin\SurveyType;
use App\Form\Transformer\BikeRideTransformer;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\SeasonService;
use Doctrine\Common\Collections\Collection;
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
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $survey = $event->getData();


        $this->modifier($event->getForm(), $survey->getRestriction(), $survey->getLevelFilter());
    }

    public function preSubmit(FormEvent $event): void
    {
        $survey = $event->getForm()->getData();

        $data = $event->getData();
        $restriction = (array_key_exists('restriction', $data)) ? $data['restriction'] : null;
        $levelFilter = (array_key_exists('levelFilter', $data) && !empty($data['levelFilter'])) ? $data['levelFilter'] : null;

        $levels = (array_key_exists('levels', $data) && !empty($data['levels'])) ? explode(';', $data['levels']) : null;
        if (array_key_exists('levelFilter', $data) || $levels) {
            $this->addOrRemoveMembers($data, $levels, $survey);
        }

        $this->cleanData($restriction, $data, $survey);
        $event->setData($data);
        $event->getForm()->setData($survey);

        $this->modifier($event->getForm(), $restriction, $levelFilter);
    }

    private function modifier(FormInterface $form, ?int $restriction, ?array $levelFilter): void
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

    private function addOrRemoveMembers(array &$data, ?array $levels, Survey $survey): void
    {
        $levelFilter = (array_key_exists('levelFilter', $data)) ? $data['levelFilter'] : null;
        if (!$levelFilter && $levels) {
            $this->clearMembers($data, $survey);
            return;
        }

        $levelsToRemove = [];
        $levelsToAdd = ($levelFilter) ? $levelFilter : [];
        
        $levelsToRemove = ($levels) ? $levels : [];
        if ($levelFilter && $levels) {
            $levelsToAdd = array_diff($levelFilter, $levels);
            $levelsToRemove = array_diff($levels, $levelFilter);
        }
        
        $membersToAdd = $this->getMembers($levelsToAdd);
        $membresToRemove = $this->getMembers($levelsToRemove);
        if (!array_key_exists('members', $data)) {
            $data['members'] = [];
        }

        foreach ($membersToAdd as $member) {
            if (!in_array($member->getId(), $data['members'])) {
                $data['members'][] = $member->getId();
            }
        }

        foreach ($membresToRemove as $member) {
            $key = array_search($member->getId(), $data['members']);
            if ($key) {
                unset($data['members'][$key]);
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
        $survey->clearMembers();
        $survey->setLevelFilter([]);
    }
}
