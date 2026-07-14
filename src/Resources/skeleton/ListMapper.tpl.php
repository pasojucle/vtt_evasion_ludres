<?= '<?php' ?>

declare(strict_types=1);

namespace App\Mapper\<?= $entity_name ?>;

use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\<?= $entity_name ?>Filter;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListView;
use App\Dto\View\ListItemView;
use App\Entity\<?= $entity_name ?>;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class <?= $entity_name ?>ListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        <?= $entity_name ?>Filter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];
        /** @var <?= $entity_name ?> $entity */
        foreach ($entities as $entity) {
            // TODO définir listItem ici
            // exemple
            // $items[] = new ListItemView(
            //     labels: [
            //         new LabelView($entity->getStartAt()->format('d/m/y')),
            //         new LabelView($entity->getTitle()),
            //     ],
            //     indicators: $this->getIndicators($entity),
            //     status: $this->getStatus($entity, $isComplete),
            //     counter: new BadgeView(
            //         $participantsTotal,
            //         $isComplete ? ColorVariant::SUCCESS : ColorVariant::DEFAULT,
            //     ),
            //     dropdown: $this->entityAdminDropdownMapper->mapToView($entity),
            //     url: $this->urlGenerator->generate("ma_route", ['<?= $entity_name ?>' => $entity->getId()]),
            // );
        }

        return new ListView(
            name: '<?= $entity_name ?>',
            title: 'Titre de la page',
            description: 'description de la page.',
            items: $items,
            settings: $this->settings(),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            // TODO Définir le bouton pour ajouter un item
            // Exemple :
            // addItem: new ButtonView(
            //     label: 'Ajouter une activité',
            //     url: $this->urlGenerator->generate('admin_bike_ride_add'),
            //     icon: 'lucide:plus',
            //     variant: ColorVariant::DEFAULT,
            // ),

            // TODO Définir le bouton pour afficher le wiki
            // Exemple :
            // wiki: new ButtonView(
            //     url: $this->urlGenerator->generate('wiki_show', ['directory' => 'boutique']),
            //     title: 'wiki',
            //     icon: 'lucide:circle-help',
            //     variant: ColorVariant::DEFAULT,
            //     htmlAttributes: [
            //         new HtmlAttributView('target', '_blank'),
            //     ],
            // ),
        );
    }


    private function settings(string $referer): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('MA_SECTION', $referer, RoundedVariant::ROUNDED, [
            //TODO Ajouter d'autre boutons si besoins
            // Exemple
            // new ButtonView(
            //     label: 'Types de rando',
            //     url: $this->urlGenerator->generate('admin_bike_ride_type_list'),
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
        //     $indicators[] = new BadgeView(
        //         value: 'lucide:users',
        //         variant: ColorVariant::ACCENT,
        //         size: Size::ICON
        //     );
        // }
        

        return $indicators;
    }
}
