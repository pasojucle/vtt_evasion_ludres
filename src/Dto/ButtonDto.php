<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;

class ButtonDto
{
    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';

    public array $htmlAttributes = [];

    public function __construct(
        public string $label,
        public string $url,
        string $turboFrame = self::TOP,
        public ?string $icon = null,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public ?string $className = null,
        public ?string $title = null,
    ) {
        $this->htmlAttributes['data-turbo-frame'] = $turboFrame;
    }

    public function addHtmlAttribut(string $name, string $value): void
    {
        $this->htmlAttributes[$name] = $value;
    }
}
