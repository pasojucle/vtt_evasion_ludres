<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SurveyFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Survey;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private PaginatorMapper $paginatorMapper,
        private SurveyAdminDropdownMapper $surveyAdminDropdownMapper,
        private FilterChipsMapper $filterChipsMapper,
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, SurveyFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $items = [];
        foreach ($entities as $entity) {
            $status = $entity->getStatus();
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getTitle()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeView(
                    $status->trans($this->translator),
                    $status->variant()
                ),
                counter: new BadgeView(
                    (string) $entity->getRespondents()->count(),
                ),
                dropdown: $this->surveyAdminDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate($entity->isAnonymous() ? 'admin_anonymous_survey' : 'admin_survey_response_list', [
                    'survey' => $entity->getId()
                ]),
                gridTemplateBadges: 'grid-cols-[1fr_1fr_30px]'
            );
        }
      
        return new ListView(
            id: 'surveys_container',
            title: 'Sondages',
            description: 'Administration des sondages : création des questionnaires et suivi des réponses.',
            items: $items,
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new ButtonView(
                label: 'Ajouter un sondage',
                url: $this->urlGenerator->generate('admin_survey_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    private function getIndicators(Survey $entity): array
    {
        $indicators = [];
        if (!$entity->getMembers()->isEmpty()) {
            $indicators[] = new BadgeView(
                value: 'lucide:users',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if ($entity->getBikeRide()) {
            $indicators[] = new BadgeView(
                value: 'lucide:bike',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if ($entity->isAnonymous()) {
            $indicators[] = new BadgeView(
                value: 'lucide:eye-off',
                variant: ColorVariant::WARNING,
                size: Size::ICON
            );
        }

        return $indicators;
    }
}
