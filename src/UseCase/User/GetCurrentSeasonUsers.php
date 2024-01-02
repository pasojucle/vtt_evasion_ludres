<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Entity\Licence;
use App\Repository\LicenceRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class GetCurrentSeasonUsers
{
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
        $usersByType = [];
        /** @var Licence $licence */
        foreach ($licencesBySeason[$season] as $licence) {
            $type = ($licence->isFinal())
                ? $this->getFinalType($licence, $licencesBySeason[$season - 1])
                : 'Inscriptions aux 3 séances d\'essai';
            if ($type) {
                $usersByType[$type][] = $licence;
            }
        }
        ksort($usersByType);
        
        return $usersByType;
    }

    private function getFinalType(Licence $licence, array $lastSeasonLicences): ?string
    {
        if (Licence::STATUS_VALID === $licence->getStatus()) {
            return 'Adhérents';
        }
        if (Licence::STATUS_WAITING_VALIDATE <= $licence->getStatus()) {
            if (array_key_exists($licence->getUser()->getId(), $lastSeasonLicences)) {
                return 'Renouvellements';
            }
            return 'Nouvelles inscriptions';
        }
        return null;
    }
}
