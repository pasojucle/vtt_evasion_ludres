<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Processor;

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
class ClusterSkillEvaluateProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
        private EvaluateMemberSkill $evaluateMemberSkill,
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
            );
        }

        $memberSkill = ($this->evaluateMemberSkill)($memberSkill, $data->evaluation);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'cluster_skill.flash.success.evaluate',
            flashType: 'success',
            data: $memberSkill,
        );
    }
}
