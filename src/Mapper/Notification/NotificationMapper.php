<?php

declare(strict_types=1);

namespace App\Mapper\Notification;

use App\Core\Contract\View\ListActionViewInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Notification;
use App\Mapper\Notification\NotificationStatusMapper;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class NotificationMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private NotificationStatusMapper $notificationStatusMapper,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function mapToView(Notification $notification, string $targetUrl): ListItemView
    {
        $tokenId = $this->csrfTokenService->getTokenId($notification);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        return new ListItemView(
            id: sprintf('notification-item-%s', $notification->getId()),
            labels: [
                new LabelView($notification->getTitle()),
                new LabelView(sprintf('Du %s au %s', $notification->getStartAt()->format('d/m/y'), $notification->getEndAt()->format('d/m/y'))),
            ],
            status: $this->notificationStatusMapper->mapToView($notification, $tokenId),
            dropdown: $this->getDropdown($notification, $targetUrl),
            action: $this->getAction($notification, $tokenValue),
            gridTemplateRow: 'grid-cols-[1fr_50px]',
            gridTemplateContent: 'grid-cols-1 lg:grid-cols-[3fr_1fr]',
            gridTemplateBadges: 'grid-cols-[fr_70px]',
            gridTemplateLabels: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
        );
    }

    private function getAction(Notification $notification, string $csrfToken): ListActionViewInterface
    {
        return new ToggleStatusView(
            url: $this->urlGenerator->generate('admin_notification_toggle', ['notification' => $notification->getId()]),
            csrfToken: $csrfToken,
            isActive: !$notification->isDisabled(),
        );
    }

    private function getDropdown(Notification $notification, string $targetUrl): DropdownView
    {
        return new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl("admin_notification_edit", ['notification' => $notification->getId()], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ],
        );
    }
}
