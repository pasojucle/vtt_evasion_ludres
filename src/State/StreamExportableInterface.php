<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\Filter\AbstractFilter;

interface StreamExportableInterface
{
    public function streamExportContent(AbstractFilter $filter): void;
}