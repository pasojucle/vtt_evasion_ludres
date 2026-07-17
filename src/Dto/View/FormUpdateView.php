<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ComponentFormViewInterface;

readonly class FormUpdateView implements ComponentFormViewInterface
{
    /**
     * @param TabView[] $tabs
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public array $tabs,
        public TabEntityInterface $entity,
        public ?string $back,
    ) {
    }

    public function getTemplate(): string
    {
        return 'components/_form_edit.html.twig';
    }

    public function getFormAttr(): array
    {
        return [
            'data-controller' => 'form-modifier',
        ];
    }
}
