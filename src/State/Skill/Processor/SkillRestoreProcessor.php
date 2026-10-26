<?php

declare(strict_types=1);

namespace App\State\Skill\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Skill;
use App\UseCase\v2\Skill\RestoreSkill;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class SkillRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreSkill $restoreSkill,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var Skill $skill
         */
        $skill = $payload->data;
        ($this->restoreSkill)($skill);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'skill.flash.success.restore',
            flashType: 'success',
            data: $skill,
        );
    }
}
