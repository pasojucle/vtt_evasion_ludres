<?php

declare(strict_types=1);

namespace App\ViewModel\Session;

use App\ViewModel\ServicesPresenter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SessionsViewModel
{
    public ?array $sessions = [];
    private ?array $sessionsByCluster = [];
    private array $bikeRides = [];

    public static function fromSessions(array|Collection|Paginator $sessions, ServicesPresenter $services): SessionsViewModel
    {
        $sessionsView = new self();
        foreach ($sessions as $session) {
            $essionView = SessionViewModel::fromSession($session, $services);
            $sessionsView->sessions[] = $essionView;
            $cluster = $session->getCluster();
            $sessionsView->sessionsByCluster[$cluster->getId()][] = $essionView;
            $bikeRide = $cluster->getBikeRide();
            $sessionsView->bikeRides[$bikeRide->getId()] = $bikeRide;
        }

        return $sessionsView;
    }

    public function bikeRideMembers(): array
    {
        $maxCount = 0;
        $clusters = [];
        $header = [];
        $rows = [];

        
        foreach ($this->bikeRides as $bikeRide) {
            foreach ($bikeRide->getClusters() as $cluster) {
                $header[] = $cluster->getTitle();
                $clusters[] = $cluster->getId();
            }
        }
        
        foreach ($this->sessionsByCluster as $sessions) {
            if ($maxCount < count($sessions)) {
                $maxCount = count($sessions);
            }
        }
        foreach ($clusters as $cluster) {
            for ($i = 0; $i < $maxCount; ++$i) {
                $session = (array_key_exists($cluster, $this->sessionsByCluster) && array_key_exists($i, $this->sessionsByCluster[$cluster]))
                    ? $this->sessionsByCluster[$cluster][$i]->user->member->fullName
                    : '';
                $rows[$i][] = $session;
            }
        }
        return ['header' => $header, 'rows' => $rows];
    }
}
