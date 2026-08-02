<?php

declare(strict_types=1);

namespace App\Dto\State;

use App\Dto\View\FlashMessageView;

readonly class TurboStreamProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public string $laziTemplate,
        public ?FlashMessageView $flashMessage = null,
    ) {
    }
}
