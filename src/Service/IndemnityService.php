<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Session;
use App\Model\Currency;
use App\Repository\IndemnityRepository;

class IndemnityService
{
    public function __construct(private IndemnityRepository $indemnityRepository)
    {

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