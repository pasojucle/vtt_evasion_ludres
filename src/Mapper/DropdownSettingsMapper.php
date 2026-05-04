<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\HtmlAttributDto;
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
     * @param ButtonDto[] $menuItems
     * @return DropdownDto
     */
    public function mapToView(string $sectionName, array $menuItems = []): DropdownDto
    {
        return  new DropdownDto(
            trigger: 'lucide:sliders-horizontal',
            position: 'relative h-8 lg:self-stretch',
            variant: ColorVariant::DEFAULT,
            menuItems: array_merge(
                $menuItems,
                $this->getParameters($sectionName),
                $this->getMessages($sectionName),
            ),
        );
    }
    
    /** @return ButtonDto[] */
    private function getParameters(string $sectionName): array 
    {
        return array_map(fn($parameter) => new ButtonDto(
            label: $parameter->getLabel(),
            url: $this->urlGenerator->generate('admin_parameter_edit', ['name' => $parameter->getName()]),
            icon: 'lucide:settings-2',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                new HtmlAttributDto('data-action', 'click->dropdown#close')
            ],
        ), $this->parameterRepository->findByParameterGroupName($sectionName));
    }

    /** @return ButtonDto[] */
    private function getMessages(string $sectionName): array
    {
        return array_map(fn($message) => new ButtonDto(
            label: $message['label'],
            url: $this->urlGenerator->generate('admin_message_edit_content', ['message' => $message['id']]),
            icon: 'lucide:message-circle',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                new HtmlAttributDto('data-action', 'click->dropdown#close')
            ],
        ), $this->messageService->getMessagesBySectionName($sectionName));
    }
}