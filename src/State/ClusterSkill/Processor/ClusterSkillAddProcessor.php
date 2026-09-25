<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Dto\Payload\ClusterSkillAddPayload;
use App\UseCase\v2\Cluster\AddClusterSkill;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class ClusterSkillAddProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private AddClusterSkill $addClusterSkill,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /** @var ClusterSkillAddPayload $data */
        $data = $payload->data;
        ($this->addClusterSkill)($data->cluster, $data->skill);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'session.flash.success.switch',
            flashType: 'success',
            data: $data->cluster,
        );
    }
}
