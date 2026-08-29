<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Processor;

use App\Dto\Payload\MemberSkillEvaluationPayload;
use App\Dto\State\TurboStreamProcessorResult;
use App\Dto\View\FlashesView;
use App\Service\CsrfTokenService;
use App\State\Interface\FormTurboStreamProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements FormTurboStreamProcessorInterface<MemberSkillEvaluationPayload>
 */
class MemberSkillEvaluationProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $memberSkill = $payload->memberSkill;
        $evaluation = $payload->evaluation;

        $tokenId = $this->csrfTokenService->getTokenId($memberSkill);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            return new TurboStreamProcessorResult(
                success: false,
                flashMessages: FlashesView::create('danger', 'Jeton CSRF invalide.'),
            );
        }

        $memberSkill->setEvaluation($evaluation);

        $this->entityManager->flush();

        
        return new TurboStreamProcessorResult(true);
    }
}
