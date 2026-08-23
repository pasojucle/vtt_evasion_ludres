<?php

declare(strict_types=1);

namespace App\State\LicenceAuthorization\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Identity;
use App\State\Interface\FormTurboStreamProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceAuthorizationsUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Identity $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $this->entityManager->flush();

        return new TurboStreamProcessorResult(true);
    }
}
