<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\DialogType;
use App\Dto\View\Interface\ComponentFormViewInterface;

readonly class DialogModalView implements ComponentFormViewInterface
{
    public function __construct(
        public DialogType $type,
        public string $title,
        public string $action,
        public string $message,
        public string $icon
    ) {
    }

    public function getTemplate(): string
    {
        return 'components/_dialog.modal.html.twig';
    }


    public function getFormAttr(): array
    {
        return [
            'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            'data-turbo-frame' => '_top'
        ];
    }
}
