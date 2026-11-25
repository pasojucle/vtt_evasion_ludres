<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Dto\Payload\SurveyToggleDto;
use App\Service\CsrfTokenService;
use App\UseCase\v2\Survey\ToggleSurvey;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class SurveyToggleProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private ToggleSurvey $toggleSurvey,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
    ) {
    }
    
    /**
     * @implements TurboStreamProcessorResult<SurveyToggleDto>
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $survey = $payload->data;
        $tokenId = $this->csrfTokenService->getTokenId($survey);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new TurboStreamProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        ($this->toggleSurvey)($survey);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: $survey->isDisabled()
                ? 'survey.flash.success.disabled'
                : 'survey.flash.success.enabled',
            flashType: 'success',
            data: $survey,
        );
    }
}
