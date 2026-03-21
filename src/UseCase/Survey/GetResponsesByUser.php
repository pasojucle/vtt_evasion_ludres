<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Dto\DtoTransformer\SurveyResponseDtoTransformer;
use App\Entity\Survey;
use App\Entity\Member;
use App\Repository\SurveyResponseRepository;

class GetResponsesByUser
{
    public function __construct(
        private SurveyResponseRepository $surveyResponseRepository,
        private SurveyResponseDtoTransformer $surveyResponseDtoTransformer
    ) {
    }
    public function execute(Survey $survey, Member $member): array
    {
        $responses = $this->surveyResponseRepository->findResponsesByUserAndSurvey($member, $survey);

        return $this->surveyResponseDtoTransformer->fromEntities($responses);
    }
}
