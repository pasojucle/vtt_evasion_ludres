<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Survey;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private RespondentRepository $respondentRepository,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        assert($entity instanceof Survey);
        $this->surveyResponseRepository->deleteBySurvey($entity);
        $this->respondentRepository->deleteBySurvey($entity);
        if ($entity->getBikeRide()) {
            $entity->getBikeRide()->setSurvey(null);
        }
        $entity->removeMembers();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.success.received',
        );
    }
}
