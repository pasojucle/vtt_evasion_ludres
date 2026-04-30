<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\HtmlAttributDto;
use App\Entity\Survey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyAdminDropdownMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    )
    {

    }

    public function mapToView(Survey $survey): DropdownDto
    {
        $menuItems = [
            new ButtonDto(
                label: 'Exporter',
                url: $this->urlGenerator->generate('admin_survey_export', ['survey' => $survey->getId()]),
                icon: 'lucide:file-down',
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Dupliquer',
                url: $this->urlGenerator->generate('admin_survey_copy', ['survey' => $survey->getId()]),
                icon: 'lucide:copy-plus',
                variant: ColorVariant::DROPDOWN,
            ),
        ];
        if (!$survey->isDisabled()) {
            $menuItems[] = new ButtonDto(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_survey_edit', ['survey' => $survey->getId()]),
                icon: 'lucide:pencil',
                variant: ColorVariant::DROPDOWN,
            );
            $menuItems[] = new ButtonDto(
                label: 'Cloturer',
                url: $this->urlGenerator->generate('admin_survey_disable', ['survey' => $survey->getId()]),
                icon: 'lucide:toggle-left',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
        }
        $menuItems[] = new ButtonDto(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_survey_delete', ['survey' => $survey->getId()]),
            icon: 'lucide:delete',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
            ],
        );

        return new DropdownDto(
            title: $survey->getTitle(),
            actionItems: [
                new DropdownItemDto(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributDto('data-email-to-clipboard-url-value', $this->urlGenerator->generate('admin_survey_email_to_clipboard')),
                        new HtmlAttributDto('data-controller', 'email-to-clipboard'),
                        new HtmlAttributDto('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                    ]
                )
            ],
            menuItems: $menuItems,
        );
    }
}