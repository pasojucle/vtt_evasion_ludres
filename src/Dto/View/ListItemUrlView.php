<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class ListItemUrlView
{
    /**
     * @param HtmlAttributeView[] $htmlAttributes
     */
    public function __construct(
        public string $path,
        public array $htmlAttributes = [
            new HtmlAttributeView('data-turbo-frame', '_top')
        ],
    ) {
    }
}
