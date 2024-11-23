<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\PracticeEnum;
use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    public const AVAILABILITY_UNDEFINED = 0;

    public const AVAILABILITY_REGISTERED = AvailabilityEnum::REGISTERED;

    public const AVAILABILITY_AVAILABLE = AvailabilityEnum::AVAILABLE;

    public const AVAILABILITY_UNAVAILABLE = AvailabilityEnum::UNAVAILABLE;

    public const AVAILABILITIES = [
        self::AVAILABILITY_REGISTERED->value => 'session.availability.registered',
        self::AVAILABILITY_AVAILABLE->value => 'session.availability.available',
        self::AVAILABILITY_UNAVAILABLE->value => 'session.availability.unavailable',
    ];

    public const BIKEKIND_VTT = 1;
    public const BIKEKIND_VTTAE = 2;
    public const BIKEKIND_ROADBIKE = 3;
    public const BIKEKIND_GRAVEL = 4;
    public const BIKEKIND_WALKING = 5;

    public const BIKEKINDS = [
        self::BIKEKIND_VTT => 'session.bike_kind.vtt',
        self::BIKEKIND_VTTAE => 'session.bike_kind.vttae',
        self::BIKEKIND_ROADBIKE => 'session.bike_kind.roadbike',
        self::BIKEKIND_GRAVEL => 'session.bike_kind.gravel',
        self::BIKEKIND_WALKING => 'session.bike_kind.walking',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Cluster::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cluster $cluster = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPresent = false;

    #[ORM\Column(type: 'Availability', nullable: true)]
    private ?AvailabilityEnum $availability = null;

    #[ORM\Column(type: 'Practice')]
    private PracticeEnum $practice = PracticeEnum::VTT;

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

    public function getAvailability(): ?AvailabilityEnum
    {
        return $this->availability;
    }

    public function setAvailability(?AvailabilityEnum $availability): self
    {
        $this->availability = $availability;

        return $this;
    }

    public function getPractice(): PracticeEnum
    {
        return $this->practice;
    }

    public function setPractice(PracticeEnum $practice): static
    {
        $this->practice = $practice;

        return $this;
    }
}
