<?php

declare(strict_types=1);

namespace App\State\EmergencyContact\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class EmergencyContactUpdateProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $this->entityManager->flush();
    
        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'member.flash.success.update',
            data: $payload->data,
        );
    }
}
