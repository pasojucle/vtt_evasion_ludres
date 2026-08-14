<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class FlashMessageView
{
    public const string SUCCESS = 'success';
    public const string DANGER = 'danger';

    public function __construct(
        public string $type,
        public string $message,
    ) {
    }
}
