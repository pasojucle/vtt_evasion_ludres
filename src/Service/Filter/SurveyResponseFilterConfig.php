<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\SurveyResponseFilter;
use App\Entity\SurveyIssue;
use App\Form\Admin\SurveyIssueAutocompleteField;
use App\Form\Filter\EventListener\SurveyIssueResponseFilterSubscriber;
use App\Repository\SurveyIssueRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SurveyResponseFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private SurveyIssueRepository $surveyIssueRepository
    ) {
    }

    public function getRouteName(): string
    {
        return 'admin_survey_response_show';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return new SurveyIssueResponseFilterSubscriber($this->surveyIssueRepository);
    }

    public function isPaginated(): bool
    {
        return false;
    }

    public function getFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'issue',
                type: SurveyIssueAutocompleteField::class,
                options: [
                    'label' => 'Question',
                    'class' => SurveyIssue::class,
                    'autocomplete_url' => 'admin_survey_issue_autocomplete',
                    'attr' => [
                        'data-action' => 'change->filter#updateContent',
                        'data-container-id' => 'survey-response-drawer'
                    ],
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                ],
                allowedFilterNames: ['survey']
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        return [];
    }

    public function getDataClass(): ?string
    {
        return SurveyResponseFilter::class;
    }
}
