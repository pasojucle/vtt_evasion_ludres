<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class SheetView implements ComponentViewInterface
{
    public function __construct(
        public string $title,
        public string $description,
        public string $action,
    ) {
    }

    public function getTemplate(): string
    {
        return 'components/_sheet.sheet.html.twig';
    }

    public function getFormAttr(): array
    {
        return [
            'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            'data-turbo-frame' => '_top'
        ];
    }
}
