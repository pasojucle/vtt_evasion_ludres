<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentFormViewInterface;
use App\Core\Contract\View\ComponentViewInterface;

readonly class SheetFormWrapperView implements ComponentFormViewInterface
{
    public function __construct(
        public string $title,
        public string $description,
        public string $action,
        public string $frameId,
        public ComponentViewInterface $formView,
    ) {
    }

    public function getName(): string
    {
        return 'sheet';
    }

    public function getTemplate(): string
    {
        return 'components/sheet/_form_wrapper.sheet.html.twig';
    }

    public function getFormAttr(): array
    {
        return [
            'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            'data-turbo-frame' => '_top'
        ];
    }
}
