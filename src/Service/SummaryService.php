<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\LogRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\SummaryRepository;

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
        /** @var User $user */
        $user = $this->security->getUser();
        $log = $this->logRepository->findOneByRouteAndUser('club_summary', $user);
        return $this->summaryRepository->findLatestDesc($log?->getViewAt());
    }
}
