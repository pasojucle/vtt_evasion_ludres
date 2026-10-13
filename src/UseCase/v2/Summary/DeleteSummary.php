<?php

declare(strict_types=1);

namespace App\UseCase\v2\Summary;

use App\Entity\Summary;
use App\Repository\Interface\SummaryRepositoryInterface;

class DeleteSummary
{
    public function __construct(
        private SummaryRepositoryInterface $summaryRepository,
    ) {
    }

    public function __invoke(Summary $summary): void
    {
        $this->summaryRepository->remove($summary);
    }
}
