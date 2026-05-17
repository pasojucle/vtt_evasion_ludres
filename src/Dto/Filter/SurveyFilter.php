<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\SurveyRestriction;
use App\Entity\Enum\SurveyStatusEnum;

class SurveyFilter extends AbstractFilter
{
    public function __construct(
        public ?SurveyStatusEnum $status = null,
        public ?SurveyRestriction $restriction = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
