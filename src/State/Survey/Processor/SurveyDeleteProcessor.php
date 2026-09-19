<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\Service\UrlContextService;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private RespondentRepository $respondentRepository,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @implements RedirectProcessorInterface<ActionPayload>
     */
    public function process(object $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $survey = $payload->data;
        $this->surveyResponseRepository->deleteBySurvey($survey);
        $this->respondentRepository->deleteBySurvey($survey);
        if ($survey->getBikeRide()) {
            $survey->getBikeRide()->setSurvey(null);
        }
        $survey->removeMembers();
        $this->entityManager->remove($survey);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'registration.flash.success.received',
        );
    }
}
