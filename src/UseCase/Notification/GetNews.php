<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Entity\User;
use App\Repository\DocumentationRepository;
use App\Repository\LinkRepository;
use App\Repository\SecondHandRepository;
use App\Repository\SlideshowImageRepository;
use App\Repository\SummaryRepository;
use App\Repository\UserSkillRepository;
use App\Service\UserService;
use Symfony\Bundle\SecurityBundle\Security;

class GetNews
{
    public function __construct(
        private readonly Security $security,
        private readonly SummaryRepository $summaryRepository,
        private readonly SlideshowImageRepository $slideshowImageRepository,
        private readonly SecondHandRepository $secondHandRepository,
        private readonly UserSkillRepository $userSkillRepository,
        private readonly LinkRepository $linkRepository,
        private readonly DocumentationRepository $documentationRepository,
        private readonly UserService $userService,
    ) {
    }

    public function getSlideShowImages(): array
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user && $this->userService->licenceIsActive($user)) {
            return $this->slideshowImageRepository->findNotViewedByUser($user);
        }
        return [];
    }

    public function getSummaries(): array
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user && $this->userService->licenceIsActive($user)) {
            return $this->summaryRepository->findNotViewedByUser($user);
        }
        return [];
    }

    public function getUserSkill(): array
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user && $this->userService->licenceIsActive($user)) {
            return $this->userSkillRepository->findNotViewedByUser($user);
        }
        return [];
    }

    public function getSecondHands(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user && $this->userService->licenceIsActive($user)) {
            return $this->secondHandRepository->findNoveltiesByUser($user);
        }
        return [];
    }

    public function getLinks(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user && $this->userService->licenceIsActive($user)) {
            return $this->linkRepository->findNoveltiesByUser($user);
        }
        return [];
    }

    public function getDocumentatons(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user && $this->userService->licenceIsActive($user)) {
            return $this->documentationRepository->findNoveltiesByUser($user);
        }
        return [];
    }
}
