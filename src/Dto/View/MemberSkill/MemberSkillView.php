<?php

declare(strict_types=1);

namespace App\Dto\View\MemberSkill;

use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class MemberSkillView implements TurboStreamViewInterface
{
    public function __construct(
        public int $id,
        public string $content,
        public LinkView $unacquired,
        public LinkView $pending,
        public LinkView $acquired,
    ) {
    }


    public function getStreamTemplate(): string
    {
        return 'member_skill/admin/update.lazy.html.twig';
    }
}
