<?php

declare(strict_types=1);

namespace App\UseCase\Coverage;

use App\UseCase\User\ExportUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class ExportCoveragesFiltered extends ExportUsersFiltered
{
    public string $filterName = 'admin_coverage_list_filters';

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findCoverageQuery($filters);
    }
}
