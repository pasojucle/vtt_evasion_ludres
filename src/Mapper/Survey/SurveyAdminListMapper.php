<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SurveyFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
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

    public function mapToView(Paginator $entities, string $route, int $currentPage, SurveyFilter $filter, FilterConfigInterface $filterConfig): ListDto
    {
        $items = [];
        foreach ($entities as $entity) {
            $status = $entity->getStatus();
            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($entity->getTitle()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeDto(
                    $status->trans($this->translator),
                    $status->variant()
                ),
                counter: new BadgeDto(
                    (string) $entity->getRespondents()->count(),
                ),
                dropdown: $this->surveyAdminDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate($entity->isAnonymous() ? 'admin_anonymous_survey' : 'admin_survey', [
                    'survey' => $entity->getId()
                ]),
            );
        }
      
        return new ListDto(
            items: $items,
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            addItem: new ButtonDto(
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
            $indicators[] = new BadgeDto(
                value: 'lucide:users',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if ($entity->getBikeRide()) {
            $indicators[] = new BadgeDto(
                value: 'lucide:bike',
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            );
        }
        if ($entity->isAnonymous()) {
            $indicators[] = new BadgeDto(
                value: 'lucide:eye-off',
                variant: ColorVariant::WARNING,
                size: Size::ICON
            );
        }

        return $indicators;
    }
}
