<?php

declare(strict_types=1);

namespace App\Dto\View\MemberSkill;

use App\Dto\View\SheetView;

readonly class MemberSkillSheetView extends SheetView
{
    public function getTemplate(): string
    {
        return 'member_skill/admin/_form.sheet.html.twig';
    }
}
