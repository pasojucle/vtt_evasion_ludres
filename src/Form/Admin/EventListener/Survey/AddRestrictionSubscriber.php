<?php

declare(strict_types=1);

namespace App\Form\Admin\EventListener\Survey;

use App\Entity\BikeRide;
use App\Entity\Enum\SurveyRestriction;
use App\Entity\Member;
use App\Entity\Survey;
use App\Form\Admin\BikeRideAutocompleteField;
use App\Form\Admin\UsersAutocompleteField;
use App\Repository\BikeRideRepository;
use App\Repository\MemberRepository;
use App\Service\LevelService;
use App\Service\SeasonService;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddRestrictionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LevelService $levelService,
        private readonly MemberRepository $memberRepository,
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
        /** @var Member $member */
        foreach ($survey->getMembers() as $member) {
            $memberIds[] = $member->getId();
        }

        $this->modifier($event->getForm(), $survey->getRestriction(), $survey->getLevelFilter(), $memberIds, $survey->getBikeRide());
    }

    public function preSubmit(FormEvent $event): void
    {
        $survey = $event->getForm()->getData();

        $data = $event->getData();
        $restriction = (array_key_exists('restriction', $data))
            ? SurveyRestriction::tryFrom($data['restriction'])
            : SurveyRestriction::ALL_MEMBERS;
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

    private function modifier(FormInterface $form, SurveyRestriction $restriction, ?array $levelFilter, ?array $memberIds, ?BikeRide $bikeRide): void
    {
        $options = $form->getConfig()->getOptions();
        $disabledMembers = SurveyRestriction::MEMBERS !== $restriction;
        $disabledBikeRide = SurveyRestriction::ACTIVITY !== $restriction;
        $disabledPeriod = SurveyRestriction::ACTIVITY === $restriction && null !== $bikeRide;
        $form
            ->add('bikeRide', BikeRideAutocompleteField::class, [
                'autocomplete_url' => $this->urlGenerator->generate('admin_bike_ride_autocomplete'),
                'required' => true,
                'attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'survey-restriction',
                ],
                'disabled' => $disabledBikeRide,
            ])
            ->add('startAt', DateType::class, [
                'input' => 'datetime',
                'label' => 'Date de début',
                'widget' => 'single_text',
                'html5' => true,
                'disabled' => $disabledPeriod,
            ])
            ->add('endAt', DateType::class, [
                'input' => 'datetime',
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => true,
                'disabled' => $disabledPeriod,
            ])
            ->add('members', UsersAutocompleteField::class, [
                'autocomplete_url' => $this->urlGenerator->generate('admin_member_autocomplete', $options['filters']),
                'required' => true,
                'disabled' => $disabledMembers,
                'attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'survey-restriction',
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
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'survey-restriction',
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
            $id = ($member instanceof Member) ? $member->getId() : $member;
            if (!in_array($id, $data['members'])) {
                $data['members'][] = $id;
            }
        }

        foreach ($membresToRemove as $member) {
            $id = ($member instanceof Member) ? $member->getId() : $member;
            $key = array_search($id, $data['members']);
            if (null !== $key) {
                unset($data['members'][$key]);
                $member = ($member instanceof Member) ? $member : $this->memberRepository->find($id);
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
            return $this->memberRepository->findMemberQuery($filters)->getQuery()->getResult();
        }
        return [];
    }

    private function cleanData(SurveyRestriction $restriction, array &$data, Survey $survey): void
    {
        if (SurveyRestriction::MEMBERS !== $restriction) {
            $this->clearMembers($data, $survey);
        }
        if (SurveyRestriction::ACTIVITY !== $restriction) {
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

    private function setPeriod(SurveyRestriction $restriction, Survey $survey, array &$data): void
    {
        $startAt = (array_key_exists('startAt', $data)) ? DateTimeImmutable::createFromFormat('Y-m-d', $data['startAt']) : new DateTimeImmutable();
        $endAt = (array_key_exists('endAt', $data)) ? DateTimeImmutable::createFromFormat('Y-m-d', $data['endAt']) : new DateTimeImmutable();

        if (SurveyRestriction::ACTIVITY === $restriction && array_key_exists('bikeRide', $data)) {
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
        $data['startAt'] = $startAt->format('Y-m-d');
        $data['endAt'] = $endAt->format('Y-m-d');
    }
}
