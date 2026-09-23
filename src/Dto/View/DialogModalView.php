<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentFormViewInterface;
use App\Dto\Enum\DialogType;

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


    public function getName(): string
    {
        return 'dialog';
    }

    public function getTemplate(): string
    {
        return 'components/_dialog.modal.html.twig';
    }


    public function getFormAttr(): array
    {
        return [
            // 'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            // 'data-turbo-frame' => '_top'
        ];
    }
}
