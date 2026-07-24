<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ComponentViewInterface;

readonly class EmailView implements ComponentViewInterface
{
    public function __construct(
        public string $value,
    ) {
    }

    public function getName(): string
    {
        return 'email';
    }

    public function getTemplate(): string
    {
        return 'components/_email.html.twig';
    }
}
