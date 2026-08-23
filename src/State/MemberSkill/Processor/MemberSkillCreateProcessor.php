<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Processor;

use App\Dto\Payload\MemberSkillEvaluationPayload;
use App\Dto\State\TurboStreamProcessorResult;
use App\State\Interface\FormTurboStreamProcessorInterface;

/**
 * @implements FormTurboStreamProcessorInterface<MemberSkillEvaluationPayload>
 */
class MemberSkillCreateProcessor implements FormTurboStreamProcessorInterface
{
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {


        return new TurboStreamProcessorResult(true);
    }
}
