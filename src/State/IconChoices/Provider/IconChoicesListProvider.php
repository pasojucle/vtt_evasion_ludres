<?php

declare(strict_types=1);

namespace App\State\IconChoices\Provider;

use App\Dto\Enum\IconChoicesAction;
use App\Dto\View\IconChoicesView;

class IconChoicesListProvider
{
    /** @param string[] $choices */
    public function getCollection(array $choices, string $value, string $action): IconChoicesView
    {
        return new IconChoicesView(
            $choices,
            $value,
            IconChoicesAction::tryFrom($action) ?? IconChoicesAction::UPDATE
        );
    }
}
