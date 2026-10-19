<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Core\Contract\Provider\DataInitializerInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\SheetFormWrapperView;
use App\Entity\Member;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Mapper\Survey\SurveyUpdateMapper;
use App\Service\SeasonService;
use DateInterval;
use DateTimeImmutable;

/**
 * @implements FormComponentProviderInterface<Survey>
 */
class SurveyCopyProvider implements FormComponentProviderInterface, DataInitializerInterface
{
    public function __construct(
        private SurveyUpdateMapper $mapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        return $this->mapper->mapToView(
            $data,
            'Copier',
            $context->tab,
        );
    }

    public function initializeData(object $data): object
    {
        $today = new DateTimeImmutable();
        $newSurvey = new Survey();

        $newSurvey->setTitle($data->getTitle())
            ->setContent($data->getContent())
            ->setStartAt($today)
            ->setEndAt($today->add(new DateInterval('P7D')))
            ->setIsAnonymous($data->isAnonymous())
            ->setLevelFilter($data->getLevelFilter());
        $this->addIssues($data, $newSurvey);
        $this->addMembres($data, $newSurvey);


        return $newSurvey;
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'issues_disabled' => false,
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
                'data-controller' => 'form-modifier',
            ]
        ];
    }

    private function addIssues(Survey $originalSurvey, Survey $survey): void
    {
        /** @var SurveyIssue $originalIssue */
        foreach ($originalSurvey->getSurveyIssues() as $originalIssue) {
            $issue = new SurveyIssue();
            $issue->setContent($originalIssue->getContent())
                ->setResponseType($originalIssue->getResponseType());
            $survey->addSurveyIssue($issue);
        }
    }

    private function addMembres(Survey $originalSurvey, Survey $survey): void
    {
        $minSeasonToTakePart = $this->seasonService->getMinSeasonToTakePart();
        /** @var Member $member */
        foreach ($originalSurvey->getMembers() as $member) {
            $lastLicence = $member->getLastLicence()->getSeason();
            if ($minSeasonToTakePart <= $lastLicence) {
                $survey->addMember($member);
            }
        }
    }
}
