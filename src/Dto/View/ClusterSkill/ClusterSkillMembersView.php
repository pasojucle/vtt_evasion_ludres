<?php

declare(strict_types=1);

namespace App\Dto\View\ClusterSkill;

use App\Core\Contract\View\TurboStreamViewInterface;
use App\Core\Dto\FlashMessage;
use App\Dto\View\LinkView;

readonly class ClusterSkillMembersView implements TurboStreamViewInterface
{
    public function __construct(
        public string $id,
        public string $fullName,
        public LinkView $unacquired,
        public LinkView $pending,
        public LinkView $acquired,
        public ?FlashMessage $flashMessage = null,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'cluster_skill/admin/member_skill_update.lazy.html.twig';
    }
}
