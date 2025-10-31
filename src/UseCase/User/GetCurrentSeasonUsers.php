<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Repository\LicenceRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class GetCurrentSeasonUsers
{
    private const MEMBER = 'Adhérents';
    private const TESTING = 'Inscriptions aux 3 séances d\'essai';
    private const REGISTRATION = 'Nouvelles inscriptions';
    private const RE_REGISTRATION = 'Renouvellements';


    public function __construct(
        private LicenceRepository $licenceRepository,
        private RequestStack $request,
    ) {
    }

    public function execute()
    {
        $season = $this->request->getSession()->get('currentSeason');

        $licencesBySeason = [];
        $licencesBySeason[$season] = [];
        /** @var Licence $licence */
        foreach ($this->licenceRepository->findAllByLastSeason() as $licence) {
            $licencesBySeason[$licence->getSeason()][$licence->getUser()->getId()] = $licence;
        }
        $usersByType = [self::MEMBER => [], self::TESTING => [], self::REGISTRATION => [], self::RE_REGISTRATION => []];
        if (array_key_exists($season, $licencesBySeason)) {
            /** @var Licence $licence */
            foreach ($licencesBySeason[$season] as $licence) {
                $type = ($licence->getState()->isYearly())
                    ? $this->getYearlyType($licence, $licencesBySeason[$season - 1])
                    : self::TESTING;
                if ($type) {
                    $usersByType[$type][] = $licence;
                }
            }
            ksort($usersByType);
        }

        return $usersByType;
    }

    private function getYearlyType(Licence $licence, array $lastSeasonLicences): ?string
    {
        if ($licence->getState()->isValid()) {
            return self::MEMBER;
        }
        if (!$licence->getState()->isValid()) {
            if (array_key_exists($licence->getUser()->getId(), $lastSeasonLicences)) {
                return self::RE_REGISTRATION;
            }
            return self::REGISTRATION;
        }
        return null;
    }
}
