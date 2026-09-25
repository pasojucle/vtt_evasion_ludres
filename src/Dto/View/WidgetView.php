<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentViewInterface;
use App\Dto\Enum\ColorVariant;

readonly class WidgetView implements ComponentViewInterface
{
    /**
     * @param LinkView[] $actions
     */
    public function __construct(
        public string $title,
        public string $value,
        public string $content,
        public string $icon,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public array $actions = [],
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
