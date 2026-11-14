<?php

declare(strict_types=1);

namespace App\State\Registration\Trait;

use App\Dto\Enum\RegistrationStatus;
use App\Dto\Filter\RegistrationFilter;
use Doctrine\ORM\QueryBuilder;

trait RegistrationListDataTrait
{
    private function getQueryBuilder(RegistrationFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();

        $currentSeason = $this->seasonService->getCurrentSeason();
        match ($filter->status) {
            RegistrationStatus::TESTING_IN_PROGRESS => $this->memberRepository->filterTestinInProgress($qb, $currentSeason),
            RegistrationStatus::TESTING_COMPLETE => $this->memberRepository->filterTestinComplete($qb, $currentSeason),
            RegistrationStatus::NEW => $this->memberRepository->filterNew($qb, $currentSeason),
            RegistrationStatus::RENEW => $this->memberRepository->filterRenew($qb, $currentSeason),
            RegistrationStatus::WAITING_RENEW => $this->memberRepository->filterWaitingRenew($qb, $currentSeason),
            RegistrationStatus::IN_PROCESSING => $this->memberRepository->filterInProcessing($qb, $currentSeason),
            RegistrationStatus::TO_REGISTER => $this->memberRepository->filterToRegister($qb, $currentSeason),
        };

        if ($filter->member) {
            $this->memberRepository->filterMember($qb, $filter->member->getId());
        }

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->memberRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->sort) {
            $this->memberRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
