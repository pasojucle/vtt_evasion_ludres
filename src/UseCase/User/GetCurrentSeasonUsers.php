<?php

declare(strict_types=1);

namespace App\UseCase\User;

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
        /** @var Licence $licence */
        foreach ($this->licenceRepository->findAllByLastSeason() as $licence) {
            $licencesBySeason[$licence->getSeason()][$licence->getUser()->getId()] = $licence;
        }
        $usersByType = [self::MEMBER => [], self::TESTING => [], self::REGISTRATION => [], self::RE_REGISTRATION => []];
        if (array_key_exists($season, $licencesBySeason)) {
            /** @var Licence $licence */
            foreach ($licencesBySeason[$season] as $licence) {
                $type = ($licence->isFinal())
                    ? $this->getFinalType($licence, $licencesBySeason[$season - 1])
                    : self::TESTING;
                if ($type) {
                    $usersByType[$type][] = $licence;
                }
            }
            ksort($usersByType);
        }

        return $usersByType;
    }

    private function getFinalType(Licence $licence, array $lastSeasonLicences): ?string
    {
        if (Licence::STATUS_VALID === $licence->getStatus()) {
            return self::MEMBER;
        }
        if (Licence::STATUS_WAITING_VALIDATE <= $licence->getStatus()) {
            if (array_key_exists($licence->getUser()->getId(), $lastSeasonLicences)) {
                return self::RE_REGISTRATION;
            }
            return self::REGISTRATION;
        }
        return null;
    }
}
