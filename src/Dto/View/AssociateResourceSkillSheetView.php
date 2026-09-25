<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\SheetView;

readonly class AssociateResourceSkillSheetView extends SheetView
{
    public function getTemplate(): string
    {
        return 'associate_resource_skill/admin/_form.sheet.html.twig';
    }
}
