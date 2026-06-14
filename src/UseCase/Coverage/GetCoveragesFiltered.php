<?php

declare(strict_types=1);

namespace App\UseCase\Coverage;

use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\DropdownItemView;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\View\HtmlAttributView;
use App\Service\LevelService;
use App\UseCase\User\GetUsersFiltered;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    
    public function settings(): ?DropdownView
    {
        return null;
    }
    
    public function tools(): ?DropdownView
    {
        return new DropdownView(
            variant: DropdownVariant::BUTTON,
            rounded: RoundedVariant::ROUNDED,
            menuItems: [
                new ButtonView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_coverages_export'),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                        new HtmlAttributView('data-turbo', 'false')
                    ],
                )
            ],
            actionItems: [
                new DropdownItemView(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributView('data-controller', 'email-to-clipboard'),
                        new HtmlAttributView('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributView('data-email-to-clipboard-url-value', $this->urlGenerator->generate('admin_coverages_email_to_clipboard')),
                    ],
                ),
            ],
        );
    }
}
