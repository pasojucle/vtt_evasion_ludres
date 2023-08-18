<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Dto\DtoTransformer\SurveyResponseDtoTransformer;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\SurveyResponseRepository;

class GetResponsesByUser
{
    public function __construct(
        private SurveyResponseRepository $surveyResponseRepository,
        private SurveyResponseDtoTransformer $surveyResponseDtoTransformer
    ) {
    }
    public function execute(Survey $survey, User $user): array
    {
        $responses = $this->surveyResponseRepository->findResponsesByUserAndSurvey($user, $survey);
        dump($responses);

        return $this->surveyResponseDtoTransformer->fromEntities($responses);
    }
}
