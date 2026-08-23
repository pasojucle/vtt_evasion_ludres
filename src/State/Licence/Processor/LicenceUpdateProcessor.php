<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\User;
use App\State\Interface\FormTurboStreamProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements FormTurboStreamProcessorInterface<User>
 */
class LicenceUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $this->entityManager->flush();
    
        return new TurboStreamProcessorResult(true);
    }
}
