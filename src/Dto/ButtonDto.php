<?php

declare(strict_types=1);

namespace App\Dto;

readonly class ButtonDto
{

    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';

    public function __construct(
        public string $label,
        public string $url,
        public string $target = self::TOP,
        public ?string $icon = null,
        public ?string $className = null,
        public ?string $title = null,
    ) 
    {

    }
}