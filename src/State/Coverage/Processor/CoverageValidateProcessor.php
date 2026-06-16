<?php

declare(strict_types=1);

namespace App\State\Coverage\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Licence;
use App\Service\FilterDecoderService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CoverageValidateProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        /** @var Licence $entity */
        $entity->setCurrentSeasonForm(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: false,
            targetRoute: 'admin_coverage_list',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'coverage.flash.success.valided',
            flashType: 'succes',
        );
    }
}
