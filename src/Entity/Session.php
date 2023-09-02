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

    public const BIKEKIND_VTT = 1;
    public const BIKEKIND_VTTAE = 2;
    public const BIKEKIND_ROADBIKE = 3;
    public const BIKEKIND_GRAVEL = 4;

    public const BIKEKINDS = [
        self::BIKEKIND_VTT => 'session.bike_kind.vtt',
        self::BIKEKIND_VTTAE => 'session.bike_kind.vttae',
        self::BIKEKIND_ROADBIKE => 'session.bike_kind.roadbike',
        self::BIKEKIND_GRAVEL => 'session.bike_kind.gravel',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: Cluster::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    private ?Cluster $cluster = null;

    #[Column(type: 'boolean')]
    private bool $isPresent = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $availability = null;

    #[Column(nullable: true)]
    private ?int $bikeKind = null;

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

    public function getBikeKind(): ?int
    {
        return $this->bikeKind;
    }

    public function setBikeKind(?int $bikeKind): static
    {
        $this->bikeKind = $bikeKind;

        return $this;
    }
}
