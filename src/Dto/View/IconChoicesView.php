<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\IconChoicesAction;

readonly class IconChoicesView
{
    /**
     * @param string[] $choices
     * @param string $value
     * @param IconChoicesAction $action
     */
    public function __construct(
        public array $choices,
        public string $value,
        public IconChoicesAction $action,
    ) {
    }
}
