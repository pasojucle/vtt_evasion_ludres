<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ListActionViewInterface;

readonly class ToggleStatusView implements ListActionViewInterface
{
    /**
     * @param string $url
     */
    public function __construct(
        public string $url,
        public string $csrfToken,
        public bool $isActive,
        public string $title = '',
    ) {
    }

    public function getName(): string
    {
        return 'toggle';
    }

    public function getTemplate(): string
    {
        return 'components/list/_toggle_status_switch.html.twig';
    }
}
