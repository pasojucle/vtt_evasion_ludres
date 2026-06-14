<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;

readonly class ButtonView
{
    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';
    public const string SHEET_CONTENT = 'sheet_content';

    /**
     * @param string $url
     * @param ColorVariant $variant
     * @param RoundedVariant $rounded
     * @param ?string $label
     * @param string|null $icon
     * @param string|null $className
     * @param HtmlAttributView[] $htmlAttributes
     */
    public function __construct(
        public string $url,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public RoundedVariant $rounded = RoundedVariant::ROUNDED,
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $className = null,
        public array $htmlAttributes = [
            new HtmlAttributView('data-turbo-frame', self::TOP)
        ],
        public string $title = '',
    ) {
    }
}
