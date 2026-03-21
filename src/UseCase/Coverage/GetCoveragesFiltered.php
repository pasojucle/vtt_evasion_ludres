<?php

declare(strict_types=1);

namespace App\UseCase\Coverage;

use App\Service\LevelService;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class GetCoveragesFiltered extends GetUsersFiltered
{
    public int $statusType = LevelService::FILTER_TYPE_COVERAGE;

    public string $statusPlaceholder = 'Toutes les saisons';

    public string $filterName = 'admin_coverage_list_filters';

    public string $remoteRoute = 'admin_coverage_autocomplete';

    public string $exportFilename = 'export_list_assurances.csv';

    public bool $statusIsRequire = false;

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->memberRepository->findCoverageQuery($filters);
    }

    public function getStatusChoices(): ?array
    {
        return null;
    }

    public function getPermissionChoices(): ?array
    {
        return null;
    }
}
