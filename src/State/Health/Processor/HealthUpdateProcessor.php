<?php

declare(strict_types=1);

namespace App\State\Health\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Identity;
use App\State\Interface\FormTurboStreamProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class HealthUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Identity $payload
     */
    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $this->entityManager->flush();

        return new TurboStreamProcessorResult(true);
    }
}
