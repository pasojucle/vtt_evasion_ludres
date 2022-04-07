<?php

declare(strict_types=1);

namespace App\UseCase\User;

use Doctrine\ORM\QueryBuilder;

class ExportMembersFiltered extends ExportUsersFiltered
{
    public string $filterName = 'admin_users_filters';

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findMemberQuery($filters);
    }
}
