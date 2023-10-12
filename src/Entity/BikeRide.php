<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BikeRideRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;

#[Entity(repositoryClass: BikeRideRepository::class)]
class BikeRide
{
    public const DEFAULT_TITLE = '';
    public const DEFAULT_DISPLAY_DURATION = 8;
    public const DEFAULT_CLOSING_DURATION = 2;

    public const PERIOD_DAY = 'jour';

    public const PERIOD_WEEK = 'semaine';

    public const PERIOD_MONTH = 'mois';

    public const PERIOD_NEXT = 'prochainement';

    public const PERIOD_ALL = 'tous';

    public const DIRECTION_PREV = 1;

    public const DIRECTION_NEXT = 2;

    public const PERIODS = [
        // self::PERIOD_DAY => 'bike_ride.period.day',
        // self::PERIOD_WEEK => 'bike_ride.period.week',
        self::PERIOD_MONTH => 'bike_ride.period.month',
        self::PERIOD_NEXT => 'bike_ride.period.next',
        self::PERIOD_ALL => 'bike_ride.period.all',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $title = '';

    #[Column(type: 'text', nullable: true)]
    private ?string $content = '';

    #[Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startAt;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endAt = null;

    #[Column(type: 'integer')]
    private int $displayDuration = self::DEFAULT_DISPLAY_DURATION;

    #[Column(type: 'integer', nullable: true)]
    private $minAge;

    #[OneToMany(targetEntity: Cluster::class, mappedBy: 'bikeRide')]
    private Collection $clusters;

    #[Column(type: 'integer', options: ['default' => 1])]
    private $closingDuration = self::DEFAULT_CLOSING_DURATION;

    #[ManyToOne(targetEntity: BikeRideType::class, inversedBy: 'bikeRides')]
    #[JoinColumn(nullable: false)]
    private BikeRideType $bikeRideType;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    #[OneToOne(mappedBy: 'bikeRide', targetEntity: Survey::class, cascade: ['persist', 'remove'])]
    private ?Survey $survey = null;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $deleted = false;

    #[ManyToMany(targetEntity: User::class, inversedBy: 'bikeRides')]
    private Collection $users;

    private ?int $restriction = null;

    #[Column(type: 'json', options:['default' => '[]'])]
    private array $levelFilter = [];

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $private = false;

    public function __construct()
    {
        $this->clusters = new ArrayCollection();
        $this->startAt = new DateTimeImmutable();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStartAt(): ?DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getDisplayDuration(): ?int
    {
        return $this->displayDuration;
    }

    public function setDisplayDuration(?int $displayDuration): self
    {
        $this->displayDuration = $displayDuration;

        return $this;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): self
    {
        $this->minAge = $minAge;

        return $this;
    }

    /**
     * @return Cluster[]|Collection
     */
    public function getClusters(): Collection
    {
        return $this->clusters;
    }

    public function addCluster(Cluster $cluster): self
    {
        if (!$this->clusters->contains($cluster)) {
            $this->clusters[] = $cluster;
            $cluster->setBikeRide($this);
        }

        return $this;
    }

    public function removeCluster(Cluster $cluster): self
    {
        if ($this->clusters->removeElement($cluster)) {
            // set the owning side to null (unless already changed)
            if ($cluster->getBikeRide() === $this) {
                $cluster->setBikeRide(null);
            }
        }

        return $this;
    }

    public function getClosingDuration(): int
    {
        return $this->closingDuration;
    }

    public function setClosingDuration(int $closingDuration): self
    {
        $this->closingDuration = $closingDuration;

        return $this;
    }

    public function getBikeRideType(): ?BikeRideType
    {
        return $this->bikeRideType;
    }

    public function setBikeRideType(?BikeRideType $bikeRideType): self
    {
        $this->bikeRideType = $bikeRideType;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        // unset the owning side of the relation if necessary
        if (null === $survey && null !== $this->survey) {
            $this->survey->setBikeRide(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $survey && $survey->getBikeRide() !== $this) {
            $survey->setBikeRide($this);
        }

        $this->survey = $survey;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function clearUsers(): self
    {
        $this->users->clear();

        return $this;
    }

    public function setRestriction(?int $restriction): self
    {
        $this->restriction = $restriction;

        return $this;
    }

    public function getRestriction(): ?int
    {
        return $this->restriction;
    }

    public function getLevelFilter(): array
    {
        return $this->levelFilter;
    }

    public function setLevelFilter(array $levelFilter): self
    {
        $this->levelFilter = $levelFilter;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }
}
