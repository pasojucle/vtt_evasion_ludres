<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\LevelType;

class AgreementFilter extends AbstractFilter
{
    public function __construct(
        public ?string $name = null,
        public ?int $itemsPerPage = null,
        public ?int $page = null,
    ) {
    }
}
