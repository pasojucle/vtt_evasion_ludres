<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;
use App\Repository\LogRepository;
use App\Repository\SummaryRepository;
use Symfony\Bundle\SecurityBundle\Security;

class SummaryService
{
    public function __construct(
        private readonly Security $security,
        private readonly LogRepository $logRepository,
        private readonly SummaryRepository $summaryRepository
    ) {
    }

    public function getSummary(): array
    {
        /** @var Member $member */
        $member = $this->security->getUser();
        $log = $this->logRepository->findOneByRouteAndUser('club_summary', $member);
        return $this->summaryRepository->findLatestDesc($log?->getViewAt());
    }
}
