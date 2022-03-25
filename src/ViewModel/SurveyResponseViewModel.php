<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;

class SurveyResponseViewModel extends AbstractViewModel
{
    public ?UserViewModel $user;

    public ?string $issue;

    public ?string $value;

    public ?string $uuid;

    public static function fromSurveyResponse(SurveyResponse $surveyResponse, array $services)
    {
        $surveyResponseView = new self();
        $surveyResponseView->issue = $surveyResponse->getSurveyIssue()->getContent();
        $surveyResponseView->user = (null !== $surveyResponse->getUser())
            ? UserViewModel::fromUser($surveyResponse->getUser(), $services)
            : null;
        $surveyResponseView->value = (SurveyIssue::RESPONSE_TYPE_CHOICE === $surveyResponse->getSurveyIssue()->getResponseType())
            ? $services['translator']->trans(SurveyResponse::VALUES[$surveyResponse->getValue()])
            : $surveyResponse->getValue();;
        $surveyResponseView->uuid = $surveyResponse->getUuid();
        
        return $surveyResponseView;
    }
}
