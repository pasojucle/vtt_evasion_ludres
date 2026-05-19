<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\NotificationRestriction;
use App\Dto\Enum\NotificationVisibility;
use App\Dto\Enum\PublishStatus;

class NotificationFilter extends AbstractFilter
{
    public function __construct(
        public ?PublishStatus $status = null,
        public ?NotificationRestriction $restriction = null,
        public ?NotificationVisibility $visibility = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
