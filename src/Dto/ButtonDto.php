<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;

class ButtonDto
{
    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';

    /**
     * @param string $label
     * @param string $url
     * @param string|null $icon
     * @param ColorVariant $variant
     * @param string|null $className
     * @param HtmlAttributDto[] $htmlAttributes
     */
    public function __construct(
        public string $label,
        public string $url,
        public ?string $icon = null,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public ?string $className = null,
        public array $htmlAttributes = [
            new HtmlAttributDto('data-turbo-frame', self::TOP)
        ],
        public string $title = '',
    ) 
    {

    }
}
