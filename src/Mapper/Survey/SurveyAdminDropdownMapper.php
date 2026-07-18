<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\LinkView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Entity\Survey;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyAdminDropdownMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(Survey $survey, string $referer): DropdownView
    {
        return new DropdownView(
            title: $survey->getTitle(),
            actionItems: [
                new DropdownItemView(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributView('data-email-to-clipboard-url-value', $this->urlGenerator->generate('admin_survey_email_to_clipboard')),
                        new HtmlAttributView('data-controller', 'email-to-clipboard'),
                        new HtmlAttributView('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                    ]
                )
            ],
            menuItems: [
                new LinkView(
                    label: 'Exporter',
                    url: $this->urlGenerator->generate('admin_survey_export', ['survey' => $survey->getId()]),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                ),
                new LinkView(
                    label: 'Dupliquer',
                    url: $this->urlGenerator->generate('admin_survey_copy', ['survey' => $survey->getId()]),
                    icon: 'lucide:copy-plus',
                    variant: ColorVariant::DROPDOWN,
                ),
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('admin_survey_delete', ['survey' => $survey->getId()], $referer),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close')
                    ],
                )
            ],
        );
    }
}
