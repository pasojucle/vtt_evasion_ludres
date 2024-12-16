<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Entity\User;
use App\Repository\SecondHandRepository;
use App\Repository\SlideshowImageRepository;
use App\Repository\SummaryRepository;
use App\Repository\UserSkillRepository;
use Symfony\Bundle\SecurityBundle\Security;

class GetNews
{
    public function __construct(
        private readonly Security $security,
        private readonly SummaryRepository $summaryRepository,
        private readonly SlideshowImageRepository $slideshowImageRepository,
        private readonly SecondHandRepository $secondHandRepository,
        private readonly UserSkillRepository $userSkillRepository,
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
            return $this->summaryRepository->findNotViewedByUser($user);
        }
        return [];
    }

    public function getUserSkill(): array
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user) {
            return $this->userSkillRepository->findNotViewedByUser($user);
        }
        return [];
    }

    public function getSecondHands(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user) {
            return $this->secondHandRepository->findNotViewedByUser($user);
        }
        return [];
    }
}
