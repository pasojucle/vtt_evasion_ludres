<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\Interface\ComponentViewInterface;

readonly class WidgetView implements ComponentViewInterface
{
    public function __construct(
        public string $title,
        public string $value,
        public string $content,
        public string $icon,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public ?LinkView $action = null,
    ) {
    }

    public function getName(): string
    {
        return 'widget';
    }

    public function getTemplate(): string
    {
        return 'components/_widget.html.twig';
    }
}
