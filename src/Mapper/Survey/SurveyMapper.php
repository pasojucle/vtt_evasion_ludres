<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Core\Contract\View\ListActionViewInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Survey;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SurveyMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private SurveyAdminDropdownMapper $surveyAdminDropdownMapper,
        private SurveyStatusMapper $surveyStatusMapper,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function mapToView(Survey $entity, string $targetUrl): ListItemView
    {
        $tokenId = $this->csrfTokenService->getTokenId($entity);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        return new ListItemView(
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
                $targetUrl,
            )),
            gridTemplateRow: 'grid-cols-[1fr_50px]',
            gridTemplateBadges: 'grid-cols-[1fr_1fr_30px]'
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
