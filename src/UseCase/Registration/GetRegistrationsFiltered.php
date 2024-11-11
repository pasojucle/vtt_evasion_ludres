<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Licence;
use App\Service\LevelService;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class GetRegistrationsFiltered extends GetUsersFiltered
{
    public int $statusType = LevelService::STATUS_TYPE_REGISTRATION;

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
            'licence.status.testing_in_processing' => Licence::STATUS_TESTING_IN_PROGRESS,
            'licence.status.testing_complete' => Licence::STATUS_TESTING_COMPLETE,
            'licence.status.new' => Licence::STATUS_NEW,
            'licence.status.renew' => Licence::STATUS_RENEW,
            'licence.status.waiting_renew' => Licence::STATUS_WAITING_RENEW,
            'licence.status.in_processing' => Licence::STATUS_IN_PROCESSING,
        ];
    }

    public function getDefaultFilters(): array
    {
        return [
            'user' => null,
            'query' => null,
            'status' => Licence::STATUS_NEW,
            'levels' => null,
        ];
    }
}
