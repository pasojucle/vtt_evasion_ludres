<?php

declare(strict_types=1);

namespace App\UseCase\Coverage;

use App\Dto\DropdownDto;
use App\Dto\RouteDto;
use App\Service\LevelService;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;


class GetCoveragesFiltered extends GetUsersFiltered
{
    public int $statusType = LevelService::FILTER_TYPE_COVERAGE;

    public string $statusPlaceholder = 'Toutes les saisons';

    public string $filterName = 'admin_coverage_list_filters';

    public string $remoteRoute = 'admin_coverage_autocomplete';

    public string $exportFilename = 'export_list_assurances.csv';

    public bool $statusIsRequire = false;

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->memberRepository->findCoverageQuery($filters);
    }

    public function getStatusChoices(): ?array
    {
        return null;
    }

    public function getPermissionChoices(): ?array
    {
        return null;
    }

    public function listFromEntities(Paginator $users): array
    {
        return $this->userDtoTransformer->coverageListFromEntities($users);
    }

    
    public function settings(): ?DropdownDto
    {
        return null;
    }
    
    public function tools(): ?DropdownDto
    {
        $dropdown = $this->dropdownDtoTransformer->fromTools();

        $dropdown->addActionItem(
            'Copier les emails de la séléction',
            'lucide:clipboard-type',
            [
                'data-controller=email-to-clipboard',
                'data-action=click->email-to-clipboard#emailToClipboard',
                sprintf('data-email-to-clipboard-url-value=%s', $this->urlGenerator->generate('admin_coverages_email_to_clipboard')),
            ],
        );

        $dropdown->addMenuItem(
            'Exporter la sélection',
            new RouteDto('admin_coverages_export'),
            'lucide:file-down',
        );
     

        return $dropdown;
    }
}
