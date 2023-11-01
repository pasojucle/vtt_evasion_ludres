<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class HealthService
{
    public function __construct(private RequestStack $requestStack)
    {
    }
    
    public function getHealthSwornCertifications(User &$user)
    {
        $swornCertifications = $this->requestStack->getSession()->get(sprintf('health__sworn_certifications_%s', $user->getLicenceNumber()));
        if (empty($swornCertifications)) {
            $swornCertifications = [];
            foreach (range(0, 2) as $number) {
                $swornCertifications[sprintf('check_up_%d',$number)] = false;
            }
        }

        $user->getHealth()->setSwornCertifications($swornCertifications);
    }
}
