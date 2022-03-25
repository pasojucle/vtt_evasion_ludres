<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Repository\SurveyResponseRepository;
use Symfony\Component\Form\Form;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetSurveyResults
{
    public function __construct(
        private TranslatorInterface $translator,
        private SurveyResponseRepository $surveyResponseRepository
    ) {
    }

    public function execute(array $filter): array
    {

        return $this->surveyResponseRepository->findByfilter($filter);
    }
}
