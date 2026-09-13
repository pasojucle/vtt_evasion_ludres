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
use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Mapper\Session\ParticipantMapper;
use App\Service\UrlContextService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClusterReadMapper
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
        int $currentSeason,
        ?string $referer,
    ): ClusterView {
        $pratice = $cluster->getPractice();
        $isSchoolActivity = $cluster->getBikeRide()->isSchoolActivity();
        $isComplete = $cluster->isComplete() ?? false;
        $allowedAvailabilities = [AvailabilityEnum::REGISTERED, AvailabilityEnum::AVAILABLE];

        $totalFramers = 0;
        $presentFramers = 0;

        $totalParticipants = 0;
        $presentParticipants = 0;

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
                $referer,
            );
            $particpants[] = $participant;

            if ($isSchoolActivity && $participant->isFramer && in_array($session->getAvailability(), $allowedAvailabilities, true)) {
                $totalFramers++;
                $presentFramers += (int) $participant->isPresent;
            } else {
                $totalParticipants++;
                $presentParticipants += (int) $participant->isPresent;
            }
        }
        
        return new ClusterView(
            id: $cluster->getId(),
            cardTitle: 'Participants',
            pratice: new BadgeView(
                value: $pratice->trans($this->translator),
                variant: $pratice->variant(),
            ),
            widgets: $this->getWidgets(
                $cluster,
                $isComplete,
                $isSchoolActivity,
                $presentParticipants,
                $totalParticipants,
                $presentFramers,
                $totalFramers,
                $referer,
            ),
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
    
    private function getWidgets(
        Cluster $cluster,
        bool $isComplete,
        bool $isSchoolActivity,
        int $presentParticipants,
        int $totalParticipants,
        int $presentFramers,
        int $totalFramers,
        string $referer,
    ): array {
        $widgets = [new WidgetView(
            title: 'Participants',
            value: (string) $presentParticipants,
            content: sprintf('Sur %d inscrits', $totalParticipants),
            icon: ($isSchoolActivity) ? LevelType::SCHOOL->getIcon() : LevelType::ADULT->getIcon(),
            action: $this->addParticipantAction($cluster, $isComplete, false, $referer),
        )];
        
        if ($isSchoolActivity) {
            $widgets[] = new WidgetView(
                title: 'Encadrants',
                value: (string) $presentFramers,
                content: sprintf('Sur %d inscrits', $totalFramers),
                icon: LevelType::FRAME->getIcon(),
                action: $this->addParticipantAction($cluster, $isComplete, true, $referer),
            );
            $widgets[] = new WidgetView(
                title: 'Compétences',
                value: (string) $cluster->getSkills()->count(),
                icon: 'lucide:badge-check',
                content: 'À évaluer',
                action: (!$isComplete)
                    ? new LinkView(
                        url: $this->urlContextService->generateUrl('admin_cluster_skills', ['cluster' => $cluster->getId()], $referer),
                        variant: ColorVariant::PRIMARY,
                        size: Size::SM,
                        label: 'Evaluer',
                        icon: 'lucide:square-check-big',
                        htmlAttributes: [
                            new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        ],
                    )
                    : null,
            );
        }

        return $widgets;
    }

    private function addParticipantAction(Cluster $cluster, bool $isComplete, bool $isFramer, string $referer): ?LinkView
    {
        if ($isComplete) {
            return null;
        }

        return new LinkView(
            url: $this->urlContextService->generateUrl('admin_session_add', [
                    'cluster' => $cluster->getId(),
                    'isFramer' => (int) $isFramer,
                ], $referer),
            variant: ColorVariant::PRIMARY,
            size: Size::SM,
            label: 'Ajouter',
            icon: 'lucide:plus',
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
            ],
        );
    }
}
