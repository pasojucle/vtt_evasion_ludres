<?php

declare(strict_types=1);

namespace App\Mapper\SurveyResponse;

use App\Dto\Enum\Size;
use App\Dto\Enum\SurveyResponseValueType;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\SurveyResponse\SurveyResponseIssueView;
use App\Dto\View\SurveyResponse\SurveyResponseListView;
use App\Dto\View\SurveyResponse\SurveyResponseTypeView;
use App\Entity\Enum\SurveyResponseType;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyResponseListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(Survey $survey, ?string $fallback): SurveyResponseListView
    {
        return new SurveyResponseListView(
            title: $survey->getTitle(),
            description: $survey->getContent(),
            fallback: new LinkView(
                $this->urlContextService->decodeUrl($fallback),
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
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
                show: (SurveyResponseType::TEXT === $entity->getResponseType() || !$survey->isAnonymous())
                    ? new LinkView(
                        url :$this->urlGenerator->generate('admin_survey_response_show', [
                            'survey' => $survey->getId(),
                            'issue' => $entity->getId()
                        ]),
                        icon:'lucide:users',
                        htmlAttributes: [
                            new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        ],
                    )
                    : null
            );
        }

        return $surveyResponsesByIssue;
    }

    private function getSurveyResponsesByType(SurveyIssue $issue): array
    {
        $responseType = $issue->getResponseType();
        $surveyResponsesByType = [];
        if (SurveyResponseType::TEXT === $responseType) {
            $surveyResponsesByType[SurveyResponseValueType::TEXT->value] = 0;
        } else {
            $surveyResponsesByType[SurveyResponseValueType::YES->value] = 0;
            $surveyResponsesByType[SurveyResponseValueType::NO->value] = 0;
        }
        if (SurveyResponseType::CHOICE === $responseType) {
            $surveyResponsesByType[SurveyResponseValueType::NO_OPINION->value] = 0;
        }

        foreach ($issue->getSurveyResponses() as $surveyResponse) {
            $valueType = SurveyResponseValueType::TEXT->value;
            if (SurveyResponseType::TEXT !== $responseType) {
                $surveyresponseValue = $surveyResponse->getValue();
                $valueType = match ($surveyresponseValue) {
                    '1' => SurveyResponseValueType::YES->value,
                    '0' => SurveyResponseValueType::NO->value,
                    '2' => SurveyResponseValueType::NO_OPINION->value,
                    default => null,
                };
            }

            if (null === $valueType) {
                continue;
            }

            ++$surveyResponsesByType[$valueType];
        }
        $types = [];
        foreach ($surveyResponsesByType as $type => $item) {
            $types[] = new SurveyResponseTypeView(
                icon: SurveyResponseValueType::tryFrom($type)->getIcon(),
                total: new BadgeView(
                    value: (string) $surveyResponsesByType[$type],
                    size: Size::MD,
                ),
            );
        }

        return $types;
    }
}
