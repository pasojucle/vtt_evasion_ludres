<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DropdownDto;
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

    public function settings(): ?DropdownDto
    {
        $dropdown = $this->dropdownDtoTransformer->fromSettings('USER');
        $dropdown->addMenuItem('Niveaux', new RouteDto('admin_levels'));
        $dropdown->addMenuItem('Compétences', new RouteDto('admin_skill_list'));
        $dropdown->addMenuItem('Roles du bureau et comité', new RouteDto('admin_board_role_list'));

        return $dropdown;
    }

    public function tools(): ?DropdownDto
    {
        $dropdown = $this->dropdownDtoTransformer->fromTools();

        $dropdown->addMenuItem(
            'Exporter la sélection',
            new RouteDto('admin_members_export'),
            'lucide:file-down',
        );
        $dropdown->addMenuItem(
            'Exporter les évaluations de la sélection',
            new RouteDto('admin_user_skill_export'),
            'lucide:file-down',
        );
        $dropdown->addMenuItem(
            'Synthèse par saison',
            new RouteDto('admin_overview_season'),
            'lucide:chart-scatter',
        );
        $dropdown->addActionItem(
            'Copier les emails de la séléction',
            'lucide:clipboard-copy',
            [
                'data-email-to-clipboard-url-value' => $this->urlGenerator->generate('admin_members_email_to_clipboard'),
                'data-controller' => 'email-to-clipboard',
                'data-action' => 'click->email-to-clipboard#emailToClipboard click->dropdown#close',
            ]
        );

        return $dropdown;
    }
}
