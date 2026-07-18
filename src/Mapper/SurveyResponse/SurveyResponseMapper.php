<?php

declare(strict_types=1);

namespace App\Mapper\SurveyResponse;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Enum\SurveyResponseValueType;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\SurveyResponse\SurveyResponseIssueView;
use App\Dto\View\SurveyResponse\SurveyResponseListView;
use App\Dto\View\SurveyResponse\SurveyResponseTypeView;
use App\Entity\Enum\SurveyResponseType;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyResponseMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToView(Survey $survey): SurveyResponseListView
    {
        return new SurveyResponseListView(
            title: $survey->getTitle(),
            description: $survey->getContent(),
            backPath: $this->urlGenerator->generate('admin_survey_response_list', ['survey' => $survey->getId()]),
            issues: $this->getSurveyResponsesByIssue($survey),
        );
    }

    private function getSurveyResponsesByIssue(Survey $survey): array
    {
        $surveyResponsesByIssue = [];
        /** @var SurveyIssue $entity */
        foreach ($survey->getSurveyIssues() as $entity) {
            $surveyResponsesByIssue[] = new SurveyResponseIssueView(
                type: $entity->getResponseType(),
                content: $entity->getContent(),
                types: $this->getSurveyResponsesByType($entity),
                show: new LinkView(
                    url :$this->urlGenerator->generate('admin_survey_response_show', [
                        'surveyResponse' => $entity->getId()
                    ]),
                    icon:'lucide:users',
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    ],
                )
            );
        }

        return $surveyResponsesByIssue;
    }

    private function getSurveyResponsesByType(SurveyIssue $issue): array
    {
        $surveyResponsesByType = [];
        foreach ($issue->getSurveyResponses() as $surveyResponse) {
            $valueRaw = null !== $surveyResponse->getValue() ? (bool) $surveyResponse->getValue() : null;
            $valueType = match (true) {
                SurveyResponseType::TEXT === $issue->getResponseType() => SurveyResponseValueType::TEXT->value,
                true === $valueRaw => SurveyResponseValueType::YES->value,
                false === $valueRaw => SurveyResponseValueType::NO->value,
                default => SurveyResponseValueType::NO_OPINION->value,
            };
            if (!array_key_exists($valueType, $surveyResponsesByType)) {
                $surveyResponsesByType[$valueType] = 0;
            }
            ++$surveyResponsesByType[$valueType];
        }
        $types = [];
        foreach ($surveyResponsesByType as $type => $item) {
            $types[] = new SurveyResponseTypeView(
                label: SurveyResponseValueType::tryFrom($type)->trans($this->translator),
                total: new BadgeView(
                    value: (string) $surveyResponsesByType[$type],
                    size: Size::MD,
                ),
            );
        }

        return $types;
    }
}
