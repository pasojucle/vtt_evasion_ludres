<?php

declare(strict_types=1);

namespace App\State\Skill\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Skill;
use App\Service\UrlContextService;
use App\UseCase\v2\Skill\DeleteSkill;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SkillDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteSkill $deleteSkill,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /**
         * @param Skill $skill
         */
        $skill = $payload->data;
        ($this->deleteSkill)($skill);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'skill.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success',
        );
    }
}
