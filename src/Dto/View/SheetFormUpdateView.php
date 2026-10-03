<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Contract\View\TurboStreamViewInterface;

readonly class SheetFormUpdateView implements TurboStreamViewInterface
{
    public function __construct(
        public string $frameId,
        public ComponentViewInterface $formView,
    ) {
    }


    public function getName(): string
    {
        return 'view';
    }


    public function getStreamTemplate(): string
    {
        return 'components/sheet/_form_update.lazy.html.twig';
    }
}
