<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\HtmlAttributDto;
use App\Dto\RouteDto;
use App\Entity\Member;
use App\Service\LevelService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class GetMembersFiltered extends GetUsersFiltered
{
    public int $statusType = LevelService::FILTER_TYPE_MEMBER;

    public string $statusPlaceholder = 'Sélectionnez une saison';

    public string $filterName = 'admin_users_filters';

    public string $remoteRoute = 'admin_member_autocomplete';

    public string $exportFilename = 'export_adherents.csv';

    public bool $statusIsRequire = false;

    public function getQuery(array $filters): QueryBuilder
    {
        return $this->memberRepository->findMemberQuery($filters);
    }

    public function getStatusChoices(): ?array
    {
        return $this->seasonService->getSeasons();
    }

    public function getPermissionChoices(): ?array
    {
        return array_flip(Member::PERMISSIONS);
    }

    public function listFromEntities(Paginator $users): array
    {
        return $this->userDtoTransformer->listFromEntities($users);
    }

    public function settings(): DropdownDto
    {
        return $this->dropdownSettingsMapper->mapToView('USER', [
            new ButtonDto(
                label: 'Niveaux',
                url: $this->urlGenerator->generate('admin_levels'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Compétences',
                url: $this->urlGenerator->generate('admin_skill_list'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Roles du bureau et comité',
                url: $this->urlGenerator->generate('admin_board_role_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    public function tools(): ?DropdownDto
    {
        return new DropdownDto(
            position: 'relative',
            menuItems: [
                new ButtonDto(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_members_export'),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close')
                    ],
                ),
                new ButtonDto(
                    label: 'Exporter les évaluations de la sélection',
                    url: $this->urlGenerator->generate('admin_user_skill_export'),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close')
                    ],
                ),
                new ButtonDto(
                    label: 'Synthèse par saison',
                    url: $this->urlGenerator->generate('admin_overview_season'),
                    icon: 'lucide:chart-scatter',
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close')
                    ],
                ),
            ],
            actionItems: [
                new DropdownItemDto(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributDto('data-controller', 'email-to-clipboard'),
                        new HtmlAttributDto('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributDto('data-email-to-clipboard-url-value', $this->urlGenerator->generate('admin_members_email_to_clipboard')),
                    ],
                ),
            ],
        );
    }
}
