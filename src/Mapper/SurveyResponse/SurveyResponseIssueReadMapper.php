<?php

declare(strict_types=1);

namespace App\Mapper\SurveyResponse;

use App\Dto\View\EmptyView;
use App\Dto\View\ListDrawerView;

;
use App\Dto\View\SurveyResponse\ListDrawerItemView;
use App\Entity\Enum\SurveyResponseType;
use App\Entity\Survey;
use App\Entity\SurveyResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyResponseIssueReadMapper
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToView(Survey $survey, array $surveyResponses, string $fallback): ListDrawerView
    {
        return new ListDrawerView(
            title: 'Réponses au sondage',
            name: 'survey-response-drawer',
            description: sprintf('Liste des réponse au sondage %s', $survey->getTitle()),
            items: array_map(function (SurveyResponse $response) {
                $identity = $response->getMember()?->getIdentity();
                $isResponseText = SurveyResponseType::TEXT === $response->getSurveyIssue()->getResponseType();
                $value = (!$isResponseText)
                    ? $this->translator->trans(SurveyResponse::VALUES[$response->getValue()])
                    : $response->getValue() ?? 'Aucune réponse';

                return  new ListDrawerItemView(
                    $value,
                    $identity?->getFullName(),
                    $isResponseText,
                );
            }, $surveyResponses),
            empty: new EmptyView(icon: 'lucide:ban', message: 'Aucune réponses')
        );
    }
}
