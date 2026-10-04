<?php

declare(strict_types=1);

namespace App\State\Cluster\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Cluster;
use App\UseCase\v2\Cluster\ToggleCompleteCluster;


/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class ClusterUnlockProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private ToggleCompleteCluster $toggleCompleteCluster,
    ) {
    }
    

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $data = $payload->data;
        /**  @var Cluster $data*/

        ($this->toggleCompleteCluster)($data, false);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'cluster.flash.success.unlock',
            flashType: 'success',
            data: $data,
        );
    }
}