<?php

declare(strict_types=1);

namespace App\UseCase\Parameter;

use App\Repository\ParameterRepository;
use App\Service\ParameterService;
use App\Service\SeasonService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class DisabledNewSeasonReRegistration
{
    public function __construct(
        private readonly ParameterService $parameterService,
        private readonly ParameterRepository $parameterRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonService $seasonService,
    ) {
    }

    public function execute(): ?array
    {
        $newSeasonReRegistration = $this->parameterRepository->findOneByName('NEW_SEASON_RE_REGISTRATION_ENABLED');
        if (!$newSeasonReRegistration->getValue()) {
            return  ['codeError' => 0,  'message' => 'New season re-registration is always desabled'];
        }
        $seasonStart = $this->parameterService->getParameterByName('SEASON_START_AT');
        $today = new DateTimeImmutable();
        $season = $this->seasonService->getCurrentSeason();

        if ($season <= $today->format('Y') && $seasonStart['month'] <= $today->format('m') && $today->format('d') <= $seasonStart['day']) {
            $newSeasonReRegistration->setValue('0');
            $this->entityManager->flush();
            return  ['codeError' => 0,  'message' => 'New season re-registration is now desabled'];
        }

        return  ['codeError' => 0,  'message' => 'New season re-registration stay enabled'];
    }
}
