<?php

declare(strict_types=1);

namespace App\State\Identity\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Identity;
use App\State\Interface\FormTurboStreamProcessorInterface;
use App\UseCase\v2\Identity\UpdateIdentity;

class IdentityUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private UpdateIdentity $updateIdentity,
    ) {
    }

    /**
     * @param Identity $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $passportPhoto = $uploadFiles['passportPhoto'] ?? null;
        $this->updateIdentity->execute($entity, $passportPhoto);

        return new TurboStreamProcessorResult('identity/admin/update.lazy.html.twig');
    }
}
