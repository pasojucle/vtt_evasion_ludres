<?= '<?php' ?>

declare(strict_types=1);

namespace App\Mapper\<?= $entity_name ?>;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\<?= $entity_name ?>Filter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\<?= $entity_name ?>;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class <?= $entity_name ?>ListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        <?= $entity_name ?>Filter $filter,
        FilterConfigInterface $filterConfig
    ): ListDto {
        $items = [];
        /** @var <?= $entity_name ?> $entity */
        foreach ($entities as $entity) {
            // TODO définir listItem ici
            // exemple
            // $items[] = new ListItemDto(
            //     labels: [
            //         new LabelDto($entity->getStartAt()->format('d/m/y')),
            //         new LabelDto($entity->getTitle()),
            //     ],
            //     indicators: $this->getIndicators($entity),
            //     status: $this->getStatus($entity, $isComplete),
            //     counter: new BadgeDto(
            //         $participantsTotal,
            //         $isComplete ? ColorVariant::SUCCESS : ColorVariant::DEFAULT,
            //     ),
            //     dropdown: $this->entityAdminDropdownMapper->mapToView($entity),
            //     url: $this->urlGenerator->generate("ma_route", ['<?= $entity_name ?>' => $entity->getId()]),
            // );
        }

        return new ListDto(
            items: $items,
            settings: $this->settings(),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => '<?= $route ?>'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            // TODO Définir le bouton pour ajouter un item
            // Exemple :
            // addItem: new ButtonDto(
            //     label: 'Ajouter une activité',
            //     url: $this->urlGenerator->generate('admin_bike_ride_add'),
            //     icon: 'lucide:plus',
            //     variant: ColorVariant::DEFAULT,
            // ),

            // TODO Définir le bouton pour afficher le wiki
            // Exemple :
            // wiki: new ButtonDto(
            //     url: $this->urlGenerator->generate('wiki_show', ['directory' => 'boutique']),
            //     title: 'wiki',
            //     icon: 'lucide:circle-help',
            //     variant: ColorVariant::DEFAULT,
            //     htmlAttributes: [
            //         new HtmlAttributDto('target', '_blank'),
            //     ],
            // ),
        );
    }


    private function settings(): DropdownDto
    {
        return $this->dropdownSettingsMapper->mapToView('MA_SECTION', RoundedVariant::ROUNDED, [
            //TODO Ajouter d'autre boutons si besoins
            // Exemple
            // new ButtonDto(
            //     label: 'Types de rando',
            //     url: $this->urlGenerator->generate('admin_bike_ride_types'),
            //     variant: ColorVariant::DROPDOWN,
            // ),
        ]);
    }

    private function getIndicators(<?= $entity_name ?> $entity): array
    {
        $indicators = [];
        // DOTO Ajouter les indicators ici
        // Exemple
        // if (!$entity->getMembers()->isEmpty()) {
        //     $indicators[] = new BadgeDto(
        //         value: 'lucide:users',
        //         variant: ColorVariant::ACCENT,
        //         size: Size::ICON
        //     );
        // }
        

        return $indicators;
    }
}
