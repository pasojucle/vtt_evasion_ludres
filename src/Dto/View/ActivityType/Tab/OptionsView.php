<?php

declare(strict_types=1);

namespace App\Dto\View\ActivityType\Tab;

use App\Core\Contract\View\TabContentInterface;

readonly class OptionsView implements TabContentInterface
{
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'activity_type/admin/edit/tab_options.html.twig';
    }
}
