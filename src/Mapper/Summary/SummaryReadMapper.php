<?php

declare(strict_types=1);

namespace App\Mapper\Summary;

use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\ActionCardView;
use App\Dto\View\DropdownView;
use App\Dto\View\EmptyView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\Summary\SummaryItemView;
use App\Dto\View\Summary\SummaryListView;
use App\Entity\BikeRide;
use App\Entity\Summary;
use App\Mapper\Activity\ActivityPeriodMapper;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SummaryReadMapper
{
    public function __construct(
        private UrlContextService $urlContext,
        private UrlGeneratorInterface $urlGenerator,
        private ActivityPeriodMapper $activityPeriodMapper,
    ) {
    }

    public function mapToView(BikeRide $activity, HandlerContext $context): SummaryListView
    {
        $targetUrl = $this->urlGenerator->generate('admin_summary_list', ['bikeRide' => $activity->getId()]);


        return new SummaryListView(
            title: $activity->getTitle(),
            description: sprintf(
                'Actualités, temps forts et photos de l\'activité %s du %s',
                $activity->getTitle(),
                $this->activityPeriodMapper->mapToView($activity)
            ),
            fallback: new LinkView(
                url: $this->urlContext->decodeUrl($context->encodedFallback),
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
            items: $activity->getSummaries()->map(fn (Summary $summary) =>
                new SummaryItemView(
                    createdAt: $summary->getCreatedAt()->format('d/m/Y'),
                    title: $summary->getTitle(),
                    content: $summary->getContent(),
                    dropdown: $this->dropdown($summary, $targetUrl),
                ))->toArray(),
            empty: new EmptyView(icon: 'lucide:image', message: 'Aucune actualité'),
            addAction: new ActionCardView(
                url: $this->urlContext->generateUrl('admin_summary_add', ['bikeRide' => $activity->getId()], $targetUrl),
                label: 'Ajouter une actualité',
                icon: 'lucide:plus',
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
        );
    }

    public function dropdown(Summary $summary, string $fallback): DropdownView
    {
        return new DropdownView(
            menuItems: [
                new LinkView(
                    url: $this->urlContext->generateUrl('admin_summary_edit', ['summary' => $summary->getId()], $fallback),
                    variant: ColorVariant::DROPDOWN,
                    label: 'Modifier',
                    icon: 'lucide:pencil',
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close')
                    ]
                ),
                new LinkView(
                    url: $this->urlContext->generateUrl('admin_summary_delete', ['summary' => $summary->getId()], $fallback),
                    variant: ColorVariant::DROPDOWN,
                    label: 'Supprimer',
                    icon: 'lucide:delete',
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close')
                    ]
                )
            ],
        );
    }
}
