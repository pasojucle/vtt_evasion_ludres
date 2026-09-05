<?php

declare(strict_types=1);

namespace App\Dto\View\Activity;

use App\Dto\View\Interface\TabEntityInterface;

readonly class ActivityView implements TabEntityInterface
{
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?string $filename,
        public ?string $filePath,
        public bool $isPublic,
    ) {
    }
}
