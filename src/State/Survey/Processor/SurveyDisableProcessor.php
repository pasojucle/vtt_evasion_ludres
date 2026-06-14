<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Survey;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use App\Service\FilterDecoderService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SurveyDisableProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private RespondentRepository $respondentRepository,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        assert($entity instanceof Survey);
        
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
