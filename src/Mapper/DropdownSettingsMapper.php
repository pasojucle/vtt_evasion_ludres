<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Repository\ParameterRepository;
use App\Service\MessageService;
use App\Service\UrlContextService;

class DropdownSettingsMapper
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param string $section
     * @param ButtonView[] $menuItems
     * @return DropdownView
     */
    public function mapToView(?string $section, string $referer, RoundedVariant $rounded, array $menuItems = []): DropdownView
    {
        return  new DropdownView(
            trigger: 'lucide:settings',
            variant: DropdownVariant::BUTTON,
            rounded: $rounded,
            menuItems: $section
                ? array_merge(
                    $menuItems,
                    $this->getParameters($section, $referer),
                    $this->getMessages($section, $referer),
                ) : $menuItems,
        );
    }
    
    /** @return ButtonView[] */
    private function getParameters(string $section, string $referer): array
    {
        return array_map(fn ($parameter) => new ButtonView(
            label: $parameter->getLabel(),
            url: $this->urlContextService->generateUrl('admin_parameter_edit', ['parameter' => $parameter->getId()], $referer),
            icon: 'lucide:settings-2',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                new HtmlAttributView('data-action', 'click->dropdown#close')
            ],
        ), $this->parameterRepository->findBySectionId($section));
    }

    /** @return ButtonView[] */
    private function getMessages(string $section, string $referer): array
    {
        return array_map(fn ($message) => new ButtonView(
            label: $message['label'],
            url: $this->urlContextService->generateUrl('admin_message_edit_content', ['message' => $message['id']], $referer),
            icon: 'lucide:message-circle',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                new HtmlAttributView('data-action', 'click->dropdown#close')
            ],
        ), $this->messageService->getMessagesBySection($section));
    }
}
