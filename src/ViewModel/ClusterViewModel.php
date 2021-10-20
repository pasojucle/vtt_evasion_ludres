<?php

namespace App\ViewModel;

use ReflectionClass;
use App\Entity\Cluster;


class ClusterViewModel extends AbstractViewModel
{
    public ?int $id;
    public ?string $title;
    public ?array $sessions;

    public static function fromCluster(Cluster $cluster, int $currentSeason)
    {
        $sessions = [];
        if (!$cluster->getSessions()->isEmpty()) {
            foreach($cluster->getSessions() as $session) {
                $sessions[] = [
                    'user' => UserViewModel::fromUser($session->getUser(), $currentSeason),
                    'availability' => $session->getAvailability(),
                    'isPresent' => $session->isPresent(),
                ];
            }
        }

        $clusterView = new self();
        $clusterView->id = $cluster->getId();
        $clusterView->title = $cluster->getTitle();
        $clusterView->sessions = $sessions;

        return $clusterView;
    }
}