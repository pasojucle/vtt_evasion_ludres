<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Licence;
use App\Entity\Respondent;
use App\Entity\User;
use App\Service\ParameterService;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use App\UseCase\BikeRide\IsRegistrable;
use App\UseCase\BikeRide\IsWritableAvailability;
use App\ViewModel\UserPresenter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class UnregistrableSessionMessage
{
    public function __construct(
        private ParameterService $parameterService,
        private SeasonService $seasonService,
        private IsRegistrable $isRegistrable,
        private IsWritableAvailability $isWritableAvailability,
        private ReplaceKeywordsService $replaceKeywordsService,
        private UserPresenter $userPresenter
    ) {
    }

    public function execute(User $user, BikeRide $bikeRide): ?string
    {
        $isRegistrable = $this->isRegistrable->execute($bikeRide, $user);
        $isWritableAvailability = $this->isWritableAvailability->execute($bikeRide, $user);

        if (!$isRegistrable && !$isWritableAvailability) {
            return 'Inscription impossible';
        }

        if (!$this->checkSeasonLicence($user)) {
            $this->userPresenter->present($user);
            return $this->replaceKeywordsService->replace($this->userPresenter->viewModel(), $this->parameterService->getParameterByName('REQUIREMENT_SEASON_LICENCE_MESSAGE'));
        }

        return null;
    }

    private function checkSeasonLicence(User $user): bool
    {
        $requirementSeasonLicenceAtParam = $this->parameterService->getParameterByName('REQUIREMENT_SEASON_LICENCE_AT');
        $seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
        $currentSeason = $this->seasonService->getCurrentSeason();
        $seasonLicence = $user->getSeasonLicence($currentSeason);

        $requirementSeasonLicenceAtParam['year'] = ($seasonStartAt['month'] <= $requirementSeasonLicenceAtParam['month']
            && $seasonStartAt['day'] <= $requirementSeasonLicenceAtParam['day'])
            ? $currentSeason - 1
            : $currentSeason;

        $requirementSeasonLicenceAt = new DateTime(implode('-', array_reverse($requirementSeasonLicenceAtParam)));
        if ($requirementSeasonLicenceAt <= new DateTime() ) {
            return $seasonLicence->isFinal() && Licence::STATUS_WAITING_VALIDATE < $seasonLicence?->getStatus();
        }
        return true;
    }
}
