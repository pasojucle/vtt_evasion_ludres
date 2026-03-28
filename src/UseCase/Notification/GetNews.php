<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Entity\Member;
use App\Entity\User;
use App\Repository\DocumentationRepository;
use App\Repository\LinkRepository;
use App\Repository\MemberSkillRepository;
use App\Repository\SecondHandRepository;
use App\Repository\SlideshowImageRepository;
use App\Repository\SummaryRepository;
use App\Service\UserService;
use Symfony\Bundle\SecurityBundle\Security;

class GetNews
{
    public function __construct(
        private readonly Security $security,
        private readonly SummaryRepository $summaryRepository,
        private readonly SlideshowImageRepository $slideshowImageRepository,
        private readonly SecondHandRepository $secondHandRepository,
        private readonly MemberSkillRepository $memberSkillRepository,
        private readonly LinkRepository $linkRepository,
        private readonly DocumentationRepository $documentationRepository,
        private readonly UserService $userService,
    ) {
    }

    public function getSlideShowImages(): array
    {
        /** @var ?User $member */
        $member = $this->security->getUser();
        if ($member instanceof Member && $this->userService->licenceIsActive($member)) {
            return $this->slideshowImageRepository->findNotViewedByUser($member);
        }
        return [];
    }

    public function getSummaries(): array
    {
        /** @var ?User $member */
        $member = $this->security->getUser();
        if ($member instanceof Member && $this->userService->licenceIsActive($member)) {
            return $this->summaryRepository->findNotViewedByUser($member);
        }
        return [];
    }

    public function getUserSkill(): array
    {
        /** @var ?User $member */
        $member = $this->security->getUser();
        if ($member instanceof Member && $this->userService->licenceIsActive($member)) {
            return $this->memberSkillRepository->findNotViewedByUser($member);
        }
        return [];
    }

    public function getSecondHands(): array
    {
        /** @var User $member */
        $member = $this->security->getUser();
        if ($member instanceof Member && $this->userService->licenceIsActive($member)) {
            return $this->secondHandRepository->findNoveltiesByUser($member);
        }
        return [];
    }

    public function getLinks(): array
    {
        /** @var User $member */
        $member = $this->security->getUser();
        if ($member instanceof Member && $this->userService->licenceIsActive($member)) {
            return $this->linkRepository->findNoveltiesByUser($member);
        }
        return [];
    }

    public function getDocumentatons(): array
    {
        /** @var User $member */
        $member = $this->security->getUser();
        if ($member instanceof Member && $this->userService->licenceIsActive($member)) {
            return $this->documentationRepository->findNoveltiesByUser($member);
        }
        return [];
    }
}
