<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ComponentFormViewInterface;

readonly class FormTabWrapperView implements ComponentFormViewInterface
{
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public array $tabs,
        public TabEntityInterface $entity,
        public ?LinkView $fallback,
        public ButtonView $submit
    ) {
    }

    public function getName(): string
    {
        return 'tabs';
    }

    public function getTemplate(): string
    {
        return 'components/tab/_form_tabs_wrapper.html.twig';
    }

    public function getFormAttr(): array
    {
        return [
            'data-controller' => 'form-modifier',
        ];
    }
}
