<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ComponentViewInterface;

readonly class PhoneView implements ComponentViewInterface
{
    public function __construct(
        public string $value,
    ) {
    }

    public function getName(): string
    {
        return 'phone';
    }

    public function getTemplate(): string
    {
        return 'components/_phone.html.twig';
    }
}
