<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\View\ListActionViewInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SurveyFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Survey;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SurveyAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private PaginatorMapper $paginatorMapper,
        private SurveyAdminDropdownMapper $surveyAdminDropdownMapper,
        private FilterChipsMapper $filterChipsMapper,
        private SurveyStatusMapper $surveyStatusMapper,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function mapToView(Paginator $entities, HandlerContext $context, SurveyFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $route = $context->route;
        $currentPage = $context->page;
        $queries = $filter->toQueryParams($currentPage);
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $queries);
        $fallback = $this->urlContextService->encodeUrl($route, $queries);

        $items = [];
        foreach ($entities as $entity) {
            $tokenId = $this->csrfTokenService->getTokenId($entity);
            $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

            $items[] = new ListItemView(
                id: sprintf('survey-item-%s', $entity->getId()),
                labels: [
                    new LabelView($entity->getTitle()),
                ],
                indicators: $this->getIndicators($entity),
                status: $this->surveyStatusMapper->mapToView($entity, $tokenId),
                action: $this->getAction($entity, $tokenValue),
                counter: new BadgeView(
                    (string) $entity->getRespondents()->count(),
                ),
                dropdown: $this->surveyAdminDropdownMapper->mapToView($entity, $targetUrl),
                url: new ListItemUrlView($this->urlContextService->generateUrl(
                    'admin_survey_response_list',
                    ['survey' => $entity->getId()],
                    $fallback,
                )),
                gridTemplateRow: 'grid-cols-[1fr_50px]',
                gridTemplateBadges: 'grid-cols-[1fr_1fr_30px]'
            );
        }
      
        return new ListView(
            name: 'survey',
            title: 'Sondages',
            description: 'Administration des sondages : création des questionnaires et suivi des réponses.',
            items: $items,
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new LinkView(
                label: 'Ajouter un sondage',
                url: $this->urlContextService->generateUrl('admin_survey_add', [], $fallback),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
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

    private function getAction(Survey $entity, string $csrfToken): ListActionViewInterface
    {
        return new ToggleStatusView(
            url: $this->urlGenerator->generate('admin_survey_toggle', ['survey' => $entity->getId()]),
            csrfToken: $csrfToken,
            isActive: !$entity->isDisabled(),
        );
    }
}
