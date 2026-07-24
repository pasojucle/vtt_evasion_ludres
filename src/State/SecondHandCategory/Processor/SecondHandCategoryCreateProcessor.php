<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\SecondHandCategory;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandCategoryCreateProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<SecondHandCategory>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'secondHandCategory.flash.success.create',
            flashType: 'success',
        );
    }
}
