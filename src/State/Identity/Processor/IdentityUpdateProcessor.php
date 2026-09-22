<?php

declare(strict_types=1);

namespace App\State\Identity\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\Identity\UpdateIdentity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class IdentityUpdateProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private UpdateIdentity $updateIdentity,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $passportPhoto = $payload->files['passportPhoto'] ?? null;
        ($this->updateIdentity)($payload->data, $passportPhoto);
        $this->entityManager->flush();

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'member.flash.success.update',
            data: $payload->data,
        );
    }
}
