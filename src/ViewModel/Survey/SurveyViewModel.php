<?php

declare(strict_types=1);

namespace App\ViewModel\Survey;

use App\Entity\Survey;
use App\Entity\SurveyResponse;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\ServicesPresenter;
use App\ViewModel\SurveyResponsesViewModel;
use App\ViewModel\UserViewModel;

class SurveyViewModel extends AbstractViewModel
{
    public ?int $id;

    public ?Survey $entity;

    public string $title = '';

    public string $content = '';

    private ServicesPresenter $services;


    
    public static function fromSurvey(Survey $survey, ServicesPresenter $services)
    {
        $surveyView = new self();
        $surveyView->id = $survey->getId();
        $surveyView->entity = $survey;
        $surveyView->title = $survey->getTitle();
        $surveyView->content = $survey->getContent();
        $surveyView->services = $services;
   

        return $surveyView;
    }

    public function getResponsesByUser(UserViewModel $user): SurveyResponsesViewModel
    {
        $responses = $this->services->entityManager->getRepository(SurveyResponse::class)->findResponsesByUserAndSurvey($user->entity, $this->entity);

        return SurveyResponsesViewModel::fromSurveyResponses($responses, $this->services);
    }
}
