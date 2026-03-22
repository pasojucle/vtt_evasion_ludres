<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

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

    #[ORM\Column(type: 'Availability', options: ['default' => AvailabilityEnum::NONE->value])]
    private AvailabilityEnum $availability = AvailabilityEnum::NONE;

    #[ORM\Column(length: 255, enumType: PracticeEnum::class)]
    private PracticeEnum $practice = PracticeEnum::NONE;

    #[ORM\Column(length: 255, enumType: BikeTypeEnum::class, options: ['default' => BikeTypeEnum::NONE->value])]
    private BikeTypeEnum $bikeType = BikeTypeEnum::NONE;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getMember(): Member
    {
        if (!$this->user instanceof Member) {
            throw new LogicException(sprintf(
                'L\'entité Session (%d) est associée à un user de type "%s", mais un "Member" était attendu.',
                $this->id,
                get_debug_type($this->user)
            ));
        }
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

    public function getAvailability(): AvailabilityEnum
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

    public function getBikeType(): BikeTypeEnum
    {
        return $this->bikeType;
    }

    public function setBikeType(BikeTypeEnum $bikeType): static
    {
        $this->bikeType = $bikeType;

        return $this;
    }
}
