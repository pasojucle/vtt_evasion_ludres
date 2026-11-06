<?php

declare(strict_types=1);

namespace App\Mapper\Participation;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\ListDrawerItemView;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Session;
use App\Mapper\Level\LevelBadgeMapper;
use App\Service\UrlContextService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActivityParticipantReadMapper
{
    public function __construct(
        private LevelBadgeMapper $levelBadgeMapper,
        private UrlContextService $urlContextService,
        private TranslatorInterface $translator,
    ) {
    }


    public function mapToView(
        Session $session,
        string $fallback,
    ): ListDrawerItemView {
        $participant = $session->getMember();
        $identity = $participant->getIdentity();
        $practice = $session->getPractice();

        return new ListDrawerItemView(
            label: $identity->getFullName(),
            indicators: [$this->levelBadgeMapper->mapToView($participant->getLevel())],
            status: new BadgeView(
                value: PracticeEnum::NONE !== $practice ? $practice->trans($this->translator) : 'Présent',
                variant: ColorVariant::SUCCESS,
            ),
            url: $this->urlContextService->generateUrl(
                'admin_user_show',
                ['user' => $participant->getId()],
                $fallback,
            ),
        );
    }
}
