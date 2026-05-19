<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;


use App\Entity\Survey;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private RespondentRepository $respondentRepository,
    ) {}

    public function process(Survey $entity): void
    {
        $this->surveyResponseRepository->deleteBySurvey($entity);
        $this->respondentRepository->deleteBySurvey($entity);
        if ($entity->getBikeRide()) {
            $entity->getBikeRide()->setSurvey(null);
        }
        $entity->removeMembers();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}