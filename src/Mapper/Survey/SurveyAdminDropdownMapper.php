<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Entity\Survey;
use App\Service\UrlContextService;

class SurveyAdminDropdownMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(Survey $survey, string $fallback): DropdownView
    {
        $menuItems = [];

        if (!$survey->isDisabled()) {
            $menuItems[] = new LinkView(
                label: 'Modifier',
                url: $this->urlContextService->generateUrl('admin_survey_edit', ['survey' => $survey->getId()], $fallback),
                icon: 'lucide:pencil',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            );
        }

        $menuItems[] = new LinkView(
            label: 'Dupliquer',
            url: $this->urlContextService->generateUrl('admin_survey_copy', ['survey' => $survey->getId()], $fallback),
            icon: 'lucide:copy-plus',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
        );

        $menuItems[] = new LinkView(
            label: 'Supprimer',
            url: $this->urlContextService->generateUrl('admin_survey_delete', ['survey' => $survey->getId()], $fallback),
            icon: 'lucide:delete',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                new HtmlAttributeView('data-action', 'click->dropdown#close')
            ],
        );

        return new DropdownView(
            title: $survey->getTitle(),
            menuItems: $menuItems,
        );
    }
}
