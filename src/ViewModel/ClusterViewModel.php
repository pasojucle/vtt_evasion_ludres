<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\Session;
use Doctrine\Common\Collections\ArrayCollection;

class ClusterViewModel extends AbstractViewModel
{
    public ?int $id;

    public ?Cluster $entity;

    public ?string $title;

    public ?Level $level;

    public ?array $sessions;

    public ?ArrayCollection $memberSessions;

    public ?ArrayCollection $availableSessions;

    public ?int $maxUsers;

    public ?string $role;

    public ?bool $isComplete;

    private ?array $services;

    public static function fromCluster(Cluster $cluster, array $services)
    {
        $clusterView = new self();
        $clusterView->services = $services;
        $clusterView->id = $cluster->getId();
        $clusterView->entity = $cluster;
        $clusterView->title = $cluster->getTitle();
        $clusterView->level = $cluster->getLevel();
        $clusterView->sessions = $clusterView->getSessions();
        $clusterView->maxUsers = $cluster->getMaxUsers();
        $clusterView->role = $cluster->getRole();
        $clusterView->isComplete = $cluster->isComplete();
        $clusterView->memberSessions = $clusterView->getMemberSessions();
        $clusterView->availableSessions = $clusterView->getAvailableSessions();
        $clusterView->usersOnSiteCount = $clusterView->getUsersOnSiteCount();

        return $clusterView;
    }

    private function getSessions(): array
    {
        $sessions = [];
        if (!$this->entity->getSessions()->isEmpty()) {
            foreach ($this->entity->getSessions() as $session) {
                $sessions[] = [
                    'user' => UserViewModel::fromUser($session->getUser(), $this->services),
                    'availability' => $session->getAvailability(),
                    'isPresent' => $session->isPresent(),
                ];
            }
        }

        return $sessions;
    }

    private function getMemberSessions(): ArrayCollection
    {
        $memberSessions = [];
        if (!$this->entity->getSessions()->isEmpty()) {
            foreach ($this->entity->getSessions() as $session) {
                $roles = $session->getUser()->getRoles();
                if (in_array('USER', $roles, true)) {
                    $memberSessions[] = $session->getUser();
                }
            }
        }

        return new ArrayCollection($memberSessions);
    }

    public function getAvailableSessions(): ArrayCollection
    {
        $sortedSessions = [];
        if (!$this->entity->getSessions()->isEmpty()) {
            foreach ($this->entity->getSessions() as $session) {
                if (Session::AVAILABILITY_UNAVAILABLE !== $session->getAvailability()) {
                    $sortedSessions[] = SessionViewModel::fromSession($session, $this->services);
                }
            }
            usort($sortedSessions, function ($a, $b) {
                $a = strtolower($a->user->member->name);
                $b = strtolower($b->user->member->name);

                if ($a === $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            });
        }

        return new ArrayCollection($sortedSessions);
    }

    public function getUsersOnSiteCount(): int
    {
        $userOnSiteSessions = [];
        if (!$this->entity->getSessions()->isEmpty()) {
            foreach ($this->entity->getSessions() as $session) {
                $level = $session->getUser()->getLevel();
                $levelType = (null !== $level) ? $level->getType() : Level::TYPE_MEMBER;
                if ($session->isPresent() && Level::TYPE_MEMBER === $levelType) {
                    $userOnSiteSessions[] = $session;
                }
            }
        }

        return count($userOnSiteSessions);
    }
}
