<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DropdownDto;
use App\Dto\RouteDto;
use App\Entity\Licence;
use App\Service\LevelService;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
        return $this->memberRepository->findLicenceInProgressQuery($filters);
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

    public function listFromEntities(Paginator $users): array
    {
        return $this->userDtoTransformer->registrationListFromEntities($users);
    }

    public function settings(): ?DropdownDto
    {
        $dropdown = $this->dropdownMapper->settingsFromSection('REGISTRATION');
        $dropdown->addMenuItem('Étapes des inscriptions', $this->urlGenerator->generate('admin_registration_step_list'));
        $dropdown->addMenuItem('Gestions des autorisations', $this->urlGenerator->generate('admin_agreement_list'));

        return $dropdown;
    }
    
    public function tools(): ?DropdownDto
    {
        $dropdown = $this->dropdownMapper->fromTools();

        $dropdown->addActionItem(
            'Copier les emails de la séléction',
            'lucide:clipboard-type',
            [
                'data-controller' => 'email-to-clipboard',
                'data-action' => 'click->email-to-clipboard#emailToClipboard click->dropdown#close',
                'data-email-to-clipboard-url-value' => $this->urlGenerator->generate('admin_registrations_email_to_clipboard'),
            ],
        );

        $dropdown->addMenuItem(
            'Exporter la sélection',
            $this->urlGenerator->generate('admin_registrations_export'),
            'lucide:file-down',
        );

        return $dropdown;
    }
}
