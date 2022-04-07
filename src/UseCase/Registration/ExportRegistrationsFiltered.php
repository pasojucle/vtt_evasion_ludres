<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\UseCase\User\ExportUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class ExportRegistrationsFiltered extends ExportUsersFiltered
{
    public string $filterName = 'admin_registrations_filters';

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findLicenceInProgressQuery($filters);
    }
}
