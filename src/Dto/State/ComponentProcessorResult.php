<?php

declare(strict_types=1);

namespace App\Dto\State;

use App\Core\Contract\View\ComponentViewInterface;

readonly class ComponentProcessorResult
{
    public function __construct(
        public bool $success,
        public string $messageKey,
        public string $flashType = 'success',
        public ?ComponentViewInterface $component = null,
    ) {
    }
}
