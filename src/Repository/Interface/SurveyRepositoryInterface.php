<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Survey;

interface SurveyRepositoryInterface
{
    public function save(Survey $summary, bool $flush = true): void;

    public function remove(Survey $summary, bool $flush = true): void;
}
