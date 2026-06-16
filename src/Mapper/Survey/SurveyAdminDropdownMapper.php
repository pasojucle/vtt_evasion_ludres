<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Entity\Survey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyAdminDropdownMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(Survey $survey): DropdownView
    {
        $menuItems = [
            new ButtonView(
                label: 'Exporter',
                url: $this->urlGenerator->generate('admin_survey_export', ['survey' => $survey->getId()]),
                icon: 'lucide:file-down',
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonView(
                label: 'Dupliquer',
                url: $this->urlGenerator->generate('admin_survey_copy', ['survey' => $survey->getId()]),
                icon: 'lucide:copy-plus',
                variant: ColorVariant::DROPDOWN,
            ),
        ];
        if (!$survey->isDisabled()) {
            $menuItems[] = new ButtonView(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_survey_edit', ['survey' => $survey->getId()]),
                icon: 'lucide:pencil',
                variant: ColorVariant::DROPDOWN,
            );
            $menuItems[] = new ButtonView(
                label: 'Cloturer',
                url: $this->urlGenerator->generate('admin_survey_disable', ['survey' => $survey->getId()]),
                icon: 'lucide:toggle-left',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            );
        }
        $menuItems[] = new ButtonView(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_survey_delete', ['survey' => $survey->getId()]),
            icon: 'lucide:delete',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                new HtmlAttributView('data-action', 'click->dropdown#close')
            ],
        );

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
            menuItems: $menuItems,
        );
    }
}
