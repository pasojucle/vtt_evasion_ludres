<?php

declare(strict_types=1);

namespace App\State\Cluster\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Cluster;
use App\Event\ClusterCompleteToggleEvent;
use App\Service\Cluster\AbsentParticipantsService;
use App\UseCase\v2\Cluster\ToggleCompleteCluster;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class ClusterCompleteProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private ToggleCompleteCluster $toggleCompleteCluster,
        private EventDispatcherInterface $eventDispatcher,
        private AbsentParticipantsService $absentParticipantsService,
    ) {
    }
    

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $data = $payload->data;
        /**  @var Cluster $data*/
        $absentParticipants = ($this->absentParticipantsService)($data);

        ($this->toggleCompleteCluster)($data, true);

        if (0 < count($absentParticipants)) {
            $this->eventDispatcher->dispatch(new ClusterCompleteToggleEvent($data->getBikeRide(), $absentParticipants));
        }

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'cluster.flash.success.complete',
            flashType: 'success',
            data: $data,
        );
    }
}
