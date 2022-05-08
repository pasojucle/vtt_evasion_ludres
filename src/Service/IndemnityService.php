<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Model\Currency;
use App\Repository\IndemnityRepository;
use App\Repository\SessionRepository;

class IndemnityService
{
    public function __construct(private IndemnityRepository $indemnityRepository, private SeasonService $seasonService, private SessionRepository $sessionRepository)
    {

    }

    public function getUserIndemnities(User $user, ?int $season = null): Currency
    {   
        if (null === $season) {
            $season = $this->seasonService->getCurrentSeason();
        }
        $query = $this->sessionRepository->findByUserAndFilters($user, ['season' => 'SEASON_'.$season]);
        /** @var QueryBuilder $query */
        $sessions = $query->getQuery()->getResult();

        return $this->getTotal($sessions);
    }

    public function getTotal(array $sessions): Currency
    {
        $allIndemnities = $this->indemnityRepository->findAll();
        $totalIndemnities = new Currency(0);
        
        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                $indemnity = $this->getIndemnity($allIndemnities, $session);
                if (null !== $indemnity) {
                    $totalIndemnities->add($indemnity);
                }
            }
        }

        return $totalIndemnities;
    }

    public function getIndemnity(array $allIndemnities, Session $session): ?Currency
    {
        if (!empty($allIndemnities)) {
            foreach ($allIndemnities as $indemnity) {
                if ($session->getCluster()->getBikeRide()->getBikeRideType() === $indemnity->getBikeRideType() && $session->getUser()->getLevel() === $indemnity->getLevel() && $session->isPresent()) {
                    $amount = new Currency($indemnity->getAmount());
                    return $amount;
                }
            }
        }
        return null;
    }
}