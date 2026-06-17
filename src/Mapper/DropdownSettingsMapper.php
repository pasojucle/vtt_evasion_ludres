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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DropdownSettingsMapper
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param string $sectionName
     * @param ButtonView[] $menuItems
     * @return DropdownView
     */
    public function mapToView(?string $sectionName, RoundedVariant $rounded, array $menuItems = []): DropdownView
    {
        return  new DropdownView(
            trigger: 'lucide:settings',
            variant: DropdownVariant::BUTTON,
            rounded: $rounded,
            menuItems: $sectionName
                ? array_merge(
                    $menuItems,
                    $this->getParameters($sectionName),
                    $this->getMessages($sectionName),
                ) : $menuItems,
        );
    }
    
    /** @return ButtonView[] */
    private function getParameters(string $sectionName): array
    {
        return array_map(fn ($parameter) => new ButtonView(
            label: $parameter->getLabel(),
            url: $this->urlGenerator->generate('admin_parameter_edit', ['name' => $parameter->getName()]),
            icon: 'lucide:settings-2',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                new HtmlAttributView('data-action', 'click->dropdown#close')
            ],
        ), $this->parameterRepository->findByParameterGroupName($sectionName));
    }

    /** @return ButtonView[] */
    private function getMessages(string $sectionName): array
    {
        return array_map(fn ($message) => new ButtonView(
            label: $message['label'],
            url: $this->urlGenerator->generate('admin_message_edit_content', ['message' => $message['id']]),
            icon: 'lucide:message-circle',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                new HtmlAttributView('data-action', 'click->dropdown#close')
            ],
        ), $this->messageService->getMessagesBySectionName($sectionName));
    }
}
