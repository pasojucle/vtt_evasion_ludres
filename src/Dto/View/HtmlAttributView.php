<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class HtmlAttributView
{
    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }
}
