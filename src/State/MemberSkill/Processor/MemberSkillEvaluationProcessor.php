<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Dto\Payload\MemberSkillEvaluationPayload;
use App\Service\CsrfTokenService;
use App\UseCase\v2\MemberSkill\EvaluateMemberSkill;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class MemberSkillEvaluationProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private EvaluateMemberSkill $evaluateMemberSkill,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var MemberSkillEvaluationPayload $data
         */
        $data = $payload->data;
        $memberSkill = $data->memberSkill;

        $tokenId = $this->csrfTokenService->getTokenId($memberSkill);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            return new TurboStreamProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
                data: $memberSkill,
            );
        }

        $memberSkill = ($this->evaluateMemberSkill)($memberSkill, $data->evaluation);

        return new TurboStreamProcessorResult(
            success: true,
            data: $memberSkill,
        );
    }
}
