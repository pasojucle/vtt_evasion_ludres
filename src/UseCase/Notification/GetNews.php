<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Entity\User;
use App\Repository\LogRepository;
use App\Repository\SecondHandRepository;
use App\Repository\SlideshowImageRepository;
use App\Repository\SummaryRepository;
use Symfony\Bundle\SecurityBundle\Security;

class GetNews
{
    public function __construct(
        private readonly Security $security,
        private readonly LogRepository $logRepository,
        private readonly SummaryRepository $summaryRepository,
        private readonly SlideshowImageRepository $slideshowImageRepository,
        private readonly SecondHandRepository $secondHandRepository,
    ) {
    }

    public function getSlideShowImages(): array
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user) {
            return $this->slideshowImageRepository->findNotViewedByUser($user);
        }
        return [];
    }

    public function getSummaries(): array
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user) {
            $log = $this->logRepository->findOneByRouteAndUser('club_summary', $user);

            return $this->summaryRepository->findLatestDesc($log?->getViewAt());
        }
        return [];
    }

    public function getSecondHands(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user) {
            $log = $this->logRepository->findOneByRouteAndUser('second_hand_list', $user);

            return $this->secondHandRepository->findSecondHandEnabled($log?->getViewAt());
        }
        return [];
    }
}
