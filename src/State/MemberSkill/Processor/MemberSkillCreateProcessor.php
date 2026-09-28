<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Processor;

use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Enum\EvaluationEnum;
use App\State\Interface\FormTurboStreamProcessorInterface;
use App\UseCase\v2\MemberSkill\CreateMemberSkill;

/**
 * @implements FormTurboStreamProcessorInterface<MemberSkillCreatePayload>
 */
class MemberSkillCreateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private CreateMemberSkill $createMemberSkill,
    ) {
    }

    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        //TODO Ajouter une unicité member + skill
        $newEntity = ($this->createMemberSkill)(
            member: $payload->member,
            skill: $payload->skill,
            evaluation: EvaluationEnum::UNACQUIRED,
        );


        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'member_skill.flash.success.create',
            // data: $newEntity
        );
    }
}
