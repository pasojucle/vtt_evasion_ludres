<?php

declare(strict_types=1);

namespace App\UseCase\Coverage;

use App\Form\UserFilterType;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class GetCoveragesFiltered extends GetUsersFiltered
{
    public int $statusType = UserFilterType::STATUS_TYPE_COVERAGE;

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findCoverageQuery($filters);
    }
}
