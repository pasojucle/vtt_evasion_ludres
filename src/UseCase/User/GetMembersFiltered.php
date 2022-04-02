<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Form\UserFilterType;
use Doctrine\ORM\QueryBuilder;

class GetMembersFiltered extends GetUsersFiltered
{
    public const STATUS_TYPE = UserFilterType::STATUS_TYPE_MEMBER;

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findMemberQuery($filters);
    }
}
