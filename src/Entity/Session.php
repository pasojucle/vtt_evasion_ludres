<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: SessionRepository::class)]
class Session
{
    public const AVAILABILITY_UNDEFINED = 0;

    public const AVAILABILITY_REGISTERED = 1;

    public const AVAILABILITY_AVAILABLE = 2;

    public const AVAILABILITY_UNAVAILABLE = 3;

    public const AVAILABILITIES = [
        self::AVAILABILITY_REGISTERED => 'session.availability.registered',
        self::AVAILABILITY_AVAILABLE => 'session.availability.available',
        self::AVAILABILITY_UNAVAILABLE => 'session.availability.unavailable',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: Cluster::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    private Cluster $cluster;

    #[Column(type: 'boolean')]
    private bool $isPresent = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $availability;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCluster(): ?Cluster
    {
        return $this->cluster;
    }

    public function setCluster(?Cluster $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function isPresent(): ?bool
    {
        return $this->isPresent;
    }

    public function setIsPresent(bool $isPresent): self
    {
        $this->isPresent = $isPresent;

        return $this;
    }

    public function getAvailability(): ?int
    {
        return $this->availability;
    }

    public function setAvailability(?int $availability): self
    {
        $this->availability = $availability;

        return $this;
    }
}
