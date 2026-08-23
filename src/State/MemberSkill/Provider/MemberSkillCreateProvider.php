<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\View\MemberKill\MemberSkillSheetView;
use App\Dto\View\SheetView;

class MemberSkillCreateProvider extends AbstractMemberSkillProvider
{
    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new MemberSkillSheetView(
            title: 'Ajouter',
            description: 'Ajouter une compétence',
            action: 'Ajouter'
        );
    }

    public function getFormOptions(object $entity): array
    {

        return [];
    }
}
