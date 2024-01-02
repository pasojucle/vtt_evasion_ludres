<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Cluster;
use Doctrine\Common\Collections\ArrayCollection;

class ClusterDto
{
    public ?int $id;

    public ?Cluster $entity;

    public ?string $title;

    public ?LevelDto $level;

    public ?array $sessions;

    public ?ArrayCollection $memberSessions;

    public ?ArrayCollection $availableSessions;

    public ?int $maxUsers;

    public ?string $role;

    public ?bool $isComplete;

    public int $usersOnSiteCount;
}
