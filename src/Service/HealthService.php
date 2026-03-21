<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;
use Symfony\Component\HttpFoundation\RequestStack;

class HealthService
{
    public function __construct(private RequestStack $requestStack)
    {
    }
    
    public function getHealthConsents(Member &$member)
    {
        $consents = $this->requestStack->getSession()->get(sprintf('health_concents_%s', $member->getLicenceNumber()));
        if (empty($consents)) {
            $consents = [];
            foreach (range(0, 2) as $number) {
                $consents[sprintf('check_up_%d', $number)] = false;
            }
        }

        $member->getHealth()->setConsents($consents);
    }
}
