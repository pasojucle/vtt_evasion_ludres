<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\BikeRide;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Service\MessageService;
use App\Service\ParameterService;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use App\UseCase\BikeRide\IsRegistrable;
use App\UseCase\BikeRide\IsWritableAvailability;
use DateTime;

class UnregistrableSessionMessage
{
    public function __construct(
        private ParameterService $parameterService,
        private MessageService $messageService,
        private IsRegistrable $isRegistrable,
        private IsWritableAvailability $isWritableAvailability,
        private ReplaceKeywordsService $replaceKeywordsService,
        private SessionRepository $sessionRepository,
        private UserDtoTransformer $userDtoTransformer,
        private SeasonService $seasonService
    ) {
    }

    public function execute(User $user, BikeRide $bikeRide): ?string
    {
        $isRegistrable = $this->isRegistrable->execute($bikeRide, $user);
        $isWritableAvailability = $this->isWritableAvailability->execute($bikeRide, $user);
        $userDto = $this->userDtoTransformer->fromEntity($user);

        if (!$isRegistrable && !$isWritableAvailability) {
            return 'Inscription impossible';
        }

        if (!$this->checkSeasonLicence($userDto)) {
            return $this->replaceKeywordsService->replace($userDto, $this->messageService->getMessageByName('REQUIREMENT_SEASON_LICENCE_MESSAGE'));
        }

        if (null !== $this->sessionRepository->findOneByUserAndBikeRide($user, $bikeRide)) {
            return 'Votre inscription a déjà été prise en compte !';
        }

        if ($userDto->isEndTesting) {
            return 'Votre période d\'essai est terminée ! Pour continuer à participer aux sorties, inscrivez-vous.';
        }

        return null;
    }

    private function checkSeasonLicence(UserDto $user): bool
    {
        $requirementSeasonLicenceAtParam = $this->parameterService->getParameterByName('REQUIREMENT_SEASON_LICENCE_AT');
        $seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
        $currentSeason = $this->seasonService->getCurrentSeason();

        $requirementSeasonLicenceAtParam['year'] = ($seasonStartAt['month'] <= $requirementSeasonLicenceAtParam['month']
            && $seasonStartAt['day'] <= $requirementSeasonLicenceAtParam['day'])
            ? $currentSeason - 1
            : $currentSeason;

        $requirementSeasonLicenceAt = new DateTime(implode('-', array_reverse($requirementSeasonLicenceAtParam)));
        if ($requirementSeasonLicenceAt <= new DateTime()) {
            return ($user->lastLicence->isSeasonLicence)
                ? Licence::STATUS_WAITING_VALIDATE <= $user->lastLicence->status
                : false;
        }
        return true;
    }
}
