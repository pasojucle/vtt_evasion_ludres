<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentViewInterface;

readonly class ListDrawerItemView implements ComponentViewInterface
{
    /**
     * @param string $label
     * @param BadgeView[] $indicators
     * @param BadgeView $status
     * @param string $url
     */
    public function __construct(
        public string $label,
        public array $indicators,
        public BadgeView $status,
        public string $url,
    ) {
    }

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'components/list/_drawer_items.html.twig';
    }
}
