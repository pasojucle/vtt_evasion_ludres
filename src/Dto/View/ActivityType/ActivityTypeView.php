<?php

declare(strict_types=1);

namespace App\Dto\View\ActivityType;

use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\ListItemView;

readonly class ActivityTypeView implements TurboStreamViewInterface
{
    public function __construct(
        public ListItemView $item,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'activity_type/admin/update.lazy.html.twig';
    }
}
