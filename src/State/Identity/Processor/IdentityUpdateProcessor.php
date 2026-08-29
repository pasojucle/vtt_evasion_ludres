<?php

declare(strict_types=1);

namespace App\State\Identity\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Identity;
use App\State\Interface\FormTurboStreamProcessorInterface;
use App\UseCase\v2\Identity\UpdateIdentity;
use Doctrine\ORM\EntityManagerInterface;

class IdentityUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private UpdateIdentity $updateIdentity,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Identity $payload
     */
    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $passportPhoto = $uploadFiles['passportPhoto'] ?? null;
        $this->updateIdentity->execute($payload, $passportPhoto);
        $this->entityManager->flush();

        return new TurboStreamProcessorResult(true);
    }
}
