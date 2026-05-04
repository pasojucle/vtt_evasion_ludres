<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;

class ButtonDto
{
    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';

    /**
     * @param string $url
     * @param ColorVariant $variant
     * @param ?string $label
     * @param string|null $icon
     * @param string|null $className
     * @param HtmlAttributDto[] $htmlAttributes
     */
    public function __construct(
        public string $url,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $className = null,
        public array $htmlAttributes = [
            new HtmlAttributDto('data-turbo-frame', self::TOP)
        ],
        public string $title = '',
    ) 
    {

    }
}
