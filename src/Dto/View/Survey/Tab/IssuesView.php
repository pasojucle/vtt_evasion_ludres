<?php

declare(strict_types=1);

namespace App\Dto\View\Survey\Tab;

use App\Core\Contract\View\TabContentInterface;

readonly class IssuesView implements TabContentInterface
{
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'survey/admin/edit/tab_issues.html.twig';
    }
}
