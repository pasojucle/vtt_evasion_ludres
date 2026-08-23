<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\View\SheetView;

class MemberSkillReadProvider extends AbstractMemberSkillProvider
{
    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le filtre de recherche',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        return [
            'data_class' => $filterConfig->getDataClass(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
        ];
    }
}
