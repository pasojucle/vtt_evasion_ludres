<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Form\UserFilterType;
use Doctrine\ORM\QueryBuilder;
use App\UseCase\User\GetUsersFiltered;

class GetRegistrationsFiltered extends GetUsersFiltered
{
    public int $statusType = UserFilterType::STATUS_TYPE_REGISTRATION;

    public string $filterName = 'admin_registrations_filters';

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findLicenceInProgressQuery($filters);
    }
}
