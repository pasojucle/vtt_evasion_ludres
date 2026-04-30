<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\SurveyStatusEnum;

class SurveyFilter extends AbstractFilter
{
    public function __construct(
        public ?SurveyStatusEnum $status = null,
    )
    {

    }
}