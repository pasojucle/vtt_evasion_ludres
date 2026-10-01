<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Dto\Payload\MemberSkillCreatePayload;
use App\Entity\Enum\EvaluationEnum;
use App\UseCase\v2\MemberSkill\CreateMemberSkill;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class MemberSkillCreateProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private CreateMemberSkill $createMemberSkill,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var MemberSkillCreatePayload $data
         */
        $data = $payload->data;

        //TODO Ajouter une unicité member + skill

        $newEntity = ($this->createMemberSkill)($data->member, $data->skill, EvaluationEnum::UNACQUIRED);


        return new TurboStreamProcessorResult(
            success: true,
            data: $newEntity
        );
    }
}
