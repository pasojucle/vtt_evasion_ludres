<?php

declare(strict_types=1);

namespace App\Dto\View\User\Tab;

use App\Dto\View\Interface\TabContentInterface;

readonly class SkillsView implements TabContentInterface
{
    public function __construct(
        public int $id,
    ) {
    }

    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'user/admin/show/tab_skills.html.twig';
    }
}
