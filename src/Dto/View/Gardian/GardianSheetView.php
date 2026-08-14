<?php

declare(strict_types=1);

namespace App\Dto\View\Gardian;

use App\Dto\View\SheetView;

readonly class GardianSheetView extends SheetView
{
    public function getTemplate(): string
    {
        return 'gardian/admin/_form.sheet.html.twig';
    }
}
