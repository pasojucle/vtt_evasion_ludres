<?php

declare(strict_types=1);

namespace App\Mapper\Cluster;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\Cluster\ClusterView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\WidgetView;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Mapper\Session\ParticipantMapper;
use App\Service\UrlContextService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClusterFrameReadMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
        private TranslatorInterface $translator,
        private ParticipantMapper $participantMapper,
    ) {
    }
    public function mapToView(
        Cluster $cluster,
        bool $isEditable,
        array $authorizationsByUser,
        array $participationsByUser,
        array $framers,
        int $currentSeason,
        string $fallback,
    ): ClusterView {
        $pratice = $cluster->getPractice();
        $isComplete = true;
        $allowedAvailabilities = [AvailabilityEnum::REGISTERED, AvailabilityEnum::AVAILABLE];

        $particpants = [];
        foreach ($cluster->getSessions() as $session) {
            $user = $session->getUser();
            $participant = $this->participantMapper->mapToView(
                $session,
                $authorizationsByUser[$user->getId()] ?? null,
                $participationsByUser[$user->getId()] ?? 0,
                $isComplete,
                $isEditable,
                $currentSeason,
                $fallback,
            );
            $availability = $session->getAvailability();
            if (in_array($availability, $allowedAvailabilities, true)) {
                $particpants[] = $participant;
            }
        }

        return new ClusterView(
            id: $cluster->getId(),
            cardTitle: 'Encadrants non affectés',
            pratice: new BadgeView(
                value: $pratice->trans($this->translator),
                variant: $pratice->variant(),
            ),
            widgets: $this->getFrameWidgets($cluster->getBikeRide(), $framers, $fallback),
            isComplete: $isComplete,
            participants: $particpants,
            isEditable: $isEditable,
            actions: [

            ],
            dropdown: new DropdownView(
                variant: DropdownVariant::GOST,
            ),
        );
    }
        
    private function getFrameWidgets(BikeRide $bikeRide, array $framers, string $referer): array
    {
        $totalFramers = (int) ($framers['count'] ?? 0);
        $totalRegistred = (int) ($framers['registered'] ?? 0);
        $totalAvailable = (int) ($framers['available'] ?? 0);
        $totalUnavailable = $totalFramers - ($totalRegistred + $totalAvailable);

        $availabilities = [
            ['enum' => AvailabilityEnum::REGISTERED, 'total' => $totalRegistred],
            ['enum' => AvailabilityEnum::AVAILABLE, 'total' => $totalAvailable],
            ['enum' => AvailabilityEnum::UNAVAILABLE, 'total' => $totalUnavailable],
        ];
        $widgets = [];
        $content = sprintf('Sur %d encadrants', $totalFramers);
        foreach ($availabilities as $availability) {
            $widgets[] = new WidgetView(
                title: $availability['enum']->trans($this->translator),
                value: (string) $availability['total'],
                icon: $availability['enum']->getIcon(),
                content: $content,
                actions: [
                    new LinkView(
                        url: $this->urlContextService->generateUrl('admin_bike_ride_framer_list', [
                            'bikeRide' => $bikeRide->getId(),
                            'availability' => $availability['enum']->value,
                            'levelType' => LevelType::FRAME->value,
                        ], $referer),
                        variant: ColorVariant::PRIMARY,
                        size: Size::SM,
                        label: 'Afficher',
                        icon: 'lucide:eye',
                        htmlAttributes: [
                            new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        ],
                    ),
                ],
            );
        }
        
        return $widgets;
    }
}
