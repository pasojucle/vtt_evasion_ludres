<?php

declare(strict_types=1);

namespace App\Dto\View\MemberStatus;

use App\Dto\View\SheetView;

readonly class MemberStatusSheetView extends SheetView
{
    public function getTemplate(): string
    {
        return 'member_status/admin/_form.sheet.html.twig';
    }
}
