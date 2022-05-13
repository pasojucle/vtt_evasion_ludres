<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Licence;
use App\Form\Admin\UserFilterType;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;

class GetRegistrationsFiltered extends GetUsersFiltered
{
    public int $statusType = UserFilterType::STATUS_TYPE_REGISTRATION;

    public string $statusPlaceholder = 'Sélectionnez une saison';

    public string $filterName = 'admin_registrations_filters';

    public string $remoteRoute = 'admin_registration_choices';

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
        ];
    }
}
