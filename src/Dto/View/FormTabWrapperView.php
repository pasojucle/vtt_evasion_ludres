<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ComponentFormViewInterface;

readonly class FormTabWrapperView extends TabWrapperView implements ComponentFormViewInterface
{
    public function __construct(
        string $name,
        string $title,
        string $description,
        array $tabs,
        TabEntityInterface $entity,
        ?LinkView $fallback,
        public ButtonView $submit
    ) {
        parent::__construct($name, $title, $description, $tabs, $entity, $fallback);
    }

    public function getName(): string
    {
        return 'tabs';
    }

    public function getTemplate(): string
    {
        return 'components/_form_tabs_wrapper.html.twig';
    }

    public function getFormAttr(): array
    {
        return [
            'data-controller' => 'form-modifier',
        ];
    }
}
