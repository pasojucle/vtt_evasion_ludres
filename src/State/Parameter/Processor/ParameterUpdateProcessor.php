<?php

declare(strict_types=1);

namespace App\State\Parameter\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Service\ParameterService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ParameterUpdateProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParameterService $parameterService,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->entityManager->flush();
        $this->parameterService->updateParamer($entity);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'Parameter.flash.success.Parameter',
            flashType: 'success',
        );
    }
}
