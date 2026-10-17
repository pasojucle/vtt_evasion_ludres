<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Survey\UpdateSurvey;

class SurveyUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateSurvey $updateSurvey,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $survey = $payload->data;
        $id = $survey->getId();
        
        ($this->updateSurvey)($survey);

        $messageKey = 'survey.flash.success.edit';
        if (!$id) {
            $messageKey = 'survey.flash.success.create';
        }
        
        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
