<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Survey;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private RespondentRepository $respondentRepository,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
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

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.success.received',
        );
    }
}
