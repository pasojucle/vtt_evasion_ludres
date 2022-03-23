<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;

class SurveyResponseViewModel extends AbstractViewModel
{
    public ?UserViewModel $user;

    public ?string $issue;

    public ?string $value;

    public ?string $uuid;

    public static function fromSurveyResponse(VoteResponse $surveyResponse, array $services)
    {
        $surveyResponseView = new self();
        $surveyResponseView->issue = $surveyResponse->getVoteIssue()->getContent();
        $surveyResponseView->user = (null !== $surveyResponse->getUser())
            ? UserViewModel::fromUser($surveyResponse->getUser(), $services)
            : null;
        $surveyResponseView->value = (VoteIssue::RESPONSE_TYPE_CHOICE === $surveyResponse->getVoteIssue()->getResponseType())
            ? $services['translator']->trans(VoteResponse::VALUES[$surveyResponse->getValue()])
            : $surveyResponse->getValue();;
        $surveyResponseView->uuid = $surveyResponse->getUuid();
        
        return $surveyResponseView;
    }
}
