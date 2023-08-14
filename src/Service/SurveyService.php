<?php

declare(strict_types=1);

namespace App\Service;

// use App\Dto\DtoTransformer\SurveyResponseDtoTransformer;
// use App\Entity\User;
// use App\Entity\Survey;
// use App\Repository\SurveyResponseRepository;

class SurveyService
{
    public function __construct(
        // private SurveyResponseRepository $surveyResponseRepository,
        // private SurveyResponseDtoTransformer $surveyResponseDtoTransformer
    ) {
    }
    // public function getResponsesByUser(Survey $survey, User $user): array
    // {
    //     $responses = $this->surveyResponseRepository->findResponsesByUserAndSurvey($user, $survey);

    //     return $this->surveyResponseDtoTransformer->fromEntities($responses);
    // }
}
