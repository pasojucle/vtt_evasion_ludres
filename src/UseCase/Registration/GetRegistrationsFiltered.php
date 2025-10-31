<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Licence;
use App\Service\LevelService;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class GetRegistrationsFiltered extends GetUsersFiltered
{
    public int $statusType = LevelService::FILTER_TYPE_REGISTRATION;

    public string $statusPlaceholder = 'Sélectionnez un statut';

    public string $filterName = 'admin_registrations_filters';

    public string $remoteRoute = 'admin_registration_autocomplete';

    public string $exportFilename = 'export_des_inscriptions.csv';

    public bool $statusIsRequire = true;

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->userRepository->findLicenceInProgressQuery($filters);
    }

    public function getStatusChoices(): ?array
    {
        return [
            'licence.filter.testing_in_processing' => Licence::FILTER_TESTING_IN_PROGRESS,
            'licence.filter.testing_complete' => Licence::FILTER_TESTING_COMPLETE,
            'licence.filter.new' => Licence::FILTER_NEW,
            'licence.filter.renew' => Licence::FILTER_RENEW,
            'licence.filter.waiting_renew' => Licence::FILTER_WAITING_RENEW,
            'licence.filter.in_processing' => Licence::FILTER_IN_PROCESSING,
            'licence.filter.to_register' => Licence::FILTER_TO_REGISTER,
        ];
    }

    public function getPermissionChoices(): ?array
    {
        return null;
    }

    public function getDefaultFilters(): array
    {
        return [
            'user' => null,
            'query' => null,
            'status' => Licence::FILTER_NEW,
            'levels' => null,
        ];
    }
}
