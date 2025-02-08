<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\User;
use App\Form\Admin\SurveyType;
use App\Service\SeasonService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class GetSurvey
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonService $seasonService,
    ) {
    }

    public function execute(?Survey &$survey): void
    {
        $this->getSurvey($survey);
        $survey->setRestriction($this->getRestriction($survey));
    }

    private function getSurvey(?Survey &$survey): void
    {
        if (!$survey) {
            $survey = new Survey();
            $survey->setStartAt(new DateTimeImmutable());
            $issue = new SurveyIssue();
            $survey->addSurveyIssue($issue);
        }
    }

    private function getRestriction(Survey $survey): ?int
    {
        return match (true) {
            null !== $survey->getBikeRide() => SurveyType::DISPLAY_BIKE_RIDE,
            !$survey->getMembers()->isEmpty() => SurveyType::DISPLAY_MEMBER_LIST,
            default => SurveyType::DISPLAY_ALL_MEMBERS
        };
    }

    public function copy(Survey $originalSurvey): Survey
    {
        $survey = new Survey();
        $survey->setTitle($originalSurvey->getTitle())
            ->setContent($originalSurvey->getContent())
            ->setIsAnonymous($originalSurvey->isAnonymous())
            ->setLevelFilter($originalSurvey->getLevelFilter());
        $this->entityManager->persist($survey);
        $this->addIssues($originalSurvey, $survey);
        $this->addMembres($originalSurvey, $survey);
        
        return $survey;
    }

    private function addIssues(Survey $originalSurvey, Survey $survey): void
    {
        /** @var SurveyIssue $originalIssue */
        foreach ($originalSurvey->getSurveyIssues() as $originalIssue) {
            $issue = new SurveyIssue();
            $issue->setContent($originalIssue->getContent())
                ->setResponseType($originalIssue->getResponseType());
            $this->entityManager->persist($issue);
            $survey->addSurveyIssue($issue);
        }
    }

    private function addMembres(Survey $originalSurvey, Survey $survey): void
    {
        $minSeasonToTakePart = $this->seasonService->getMinSeasonToTakePart();
        /** @var User $member */
        foreach ($originalSurvey->getMembers() as $member) {
            $lastLicence = $member->getLastLicence()->getSeason();
            if ($minSeasonToTakePart <= $lastLicence) {
                $survey->addMember($member);
            }
        }
    }
}
