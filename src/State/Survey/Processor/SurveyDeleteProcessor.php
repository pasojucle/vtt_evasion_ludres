<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Survey;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private RespondentRepository $respondentRepository,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<Survey>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        ;
        $this->surveyResponseRepository->deleteBySurvey($entity);
        $this->respondentRepository->deleteBySurvey($entity);
        if ($entity->getBikeRide()) {
            $entity->getBikeRide()->setSurvey(null);
        }
        $entity->removeMembers();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.success.received',
        );
    }
}
