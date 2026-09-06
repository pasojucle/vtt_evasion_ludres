<?php

declare(strict_types=1);

namespace App\Dto\View\Session;

use App\Dto\View\SheetView;

readonly class SessionAddSheetView extends SheetView
{
    public function getTemplate(): string
    {
        return 'session/admin/add/_form.sheet.html.twig';
    }
}
