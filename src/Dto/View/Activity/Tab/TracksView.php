<?php

declare(strict_types=1);

namespace App\Dto\View\Activity\Tab;

use App\Core\Contract\View\TabContentInterface;

readonly class TracksView implements TabContentInterface
{
    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'activity/admin/edit/tab_tracks.html.twig';
    }
}
