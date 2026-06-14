<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Survey;
use App\Service\FilterDecoderService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDisableProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        /** @var Survey $entity */
        $entity->setDisabled(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetRoute: 'admin_survey_list',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'registration.flash.success.received',
        );
    }
}
