<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Service\LevelService;
use Doctrine\ORM\QueryBuilder;

class GetMembersFiltered extends GetUsersFiltered
{
    public int $statusType = LevelService::STATUS_TYPE_MEMBER;

    public string $statusPlaceholder = 'Sélectionnez une saison';

    public string $filterName = 'admin_users_filters';

    public string $remoteRoute = 'admin_member_choices';

    public string $exportFilename = 'export_adherents.csv';

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findMemberQuery($filters);
    }

    public function getStatusChoices(): ?array
    {
        return $this->seasonService->getSeasons();
    }
}
