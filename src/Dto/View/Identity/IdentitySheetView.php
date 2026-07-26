<?php

declare(strict_types=1);

namespace App\Dto\View\Identity;

use App\Dto\View\SheetView;

readonly class IdentitySheetView extends SheetView
{

    public function getTemplate(): string
    {
        return 'identity/admin/_form.sheet.html.twig';
    }
}
