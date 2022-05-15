<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Cluster;
use Doctrine\Common\Collections\ArrayCollection;


class ClusterService
{
    public function getMemberSessions(Cluster $cluster): ArrayCollection
    {
        $memberSessions = [];
        if (!$cluster->getSessions()->isEmpty()) {
            foreach ($cluster->getSessions() as $session) {
                $roles = $session->getUser()->getRoles();
                if (in_array('USER', $roles, true)) {
                    $memberSessions[] = $session->getUser();
                }
            }
        }

        return new ArrayCollection($memberSessions);
    }
}
