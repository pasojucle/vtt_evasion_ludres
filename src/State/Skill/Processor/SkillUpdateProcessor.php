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
use App\UseCase\v2\Skill\UpdateSkill;

class SkillUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateSkill $updateSkill,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var Skill $skill*/
        $skill = $payload->data;
        $id = $skill->getId();

        ($this->updateSkill)($skill);

        $messageKey = 'skill.flash.success.edit';
        if (!$id) {
            $messageKey = 'skill.flash.success.create';
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
