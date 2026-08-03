<?php

declare(strict_types=1);

namespace App\Dto\View\LicenceAuthorization;

use App\Dto\View\SheetView;

readonly class LicenceAuthorizationsSheetView extends SheetView
{

    public function getTemplate(): string
    {
        return 'licence_authorization/admin/_form.sheet.html.twig';
    }
}
