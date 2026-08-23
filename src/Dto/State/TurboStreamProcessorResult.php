<?php

declare(strict_types=1);

namespace App\Dto\State;

use App\Dto\View\FlashesView;

readonly class TurboStreamProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public bool $success,
        public ?FlashesView $flashMessages = null,
    ) {
    }
}
