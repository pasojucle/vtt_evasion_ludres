<?php

declare(strict_types=1);

namespace App\Dto\View\Activity\Tab;

use App\Dto\View\Interface\TabContentInterface;

readonly class OptionsView implements TabContentInterface
{
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate():string
    {
        return 'activity/admin/edit/tab_options.html.twig';
    }
}