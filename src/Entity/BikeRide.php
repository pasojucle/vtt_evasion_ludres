<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BikeRideRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BikeRideRepository::class)]
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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 150)]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endAt = null;

    #[ORM\Column(type: 'integer')]
    private int $displayDuration = self::DEFAULT_DISPLAY_DURATION;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $minAge;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxAge = null;

    #[ORM\OneToMany(targetEntity: Cluster::class, mappedBy: 'bikeRide')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $clusters;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $closingDuration = self::DEFAULT_CLOSING_DURATION;

    #[ORM\ManyToOne(targetEntity: BikeRideType::class, inversedBy: 'bikeRides')]
    #[ORM\JoinColumn(nullable: false)]
    private BikeRideType $bikeRideType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\OneToOne(mappedBy: 'bikeRide', targetEntity: Survey::class, cascade: ['persist', 'remove'])]
    private ?Survey $survey = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'bikeRides')]
    private Collection $users;

    private ?int $restriction = null;

    #[ORM\Column(type: 'json', options:['default' => '[]'])]
    private array $levelFilter = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $private = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $registrationEnabled = true;

    #[ORM\OneToMany(mappedBy: 'bikeRide', targetEntity: Summary::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $summaries;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $notify = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $registrationClosedMessage = null;

    public function __construct()
    {
        $this->clusters = new ArrayCollection();
        $this->startAt = new DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->summaries = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->startAt->format('d/m/y'), $this->title);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStartAt(): ?DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getDisplayDuration(): ?int
    {
        return $this->displayDuration;
    }

    public function setDisplayDuration(?int $displayDuration): static
    {
        $this->displayDuration = $displayDuration;

        return $this;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): static
    {
        $this->minAge = $minAge;

        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): static
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * @return Cluster[]|Collection
     */
    public function getClusters(): Collection
    {
        return $this->clusters;
    }

    public function addCluster(Cluster $cluster): static
    {
        if (!$this->clusters->contains($cluster)) {
            $this->clusters[] = $cluster;
            $cluster->setBikeRide($this);
        }

        return $this;
    }

    public function removeCluster(Cluster $cluster): static
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

    public function setClosingDuration(int $closingDuration): static
    {
        $this->closingDuration = $closingDuration;

        return $this;
    }

    public function getBikeRideType(): ?BikeRideType
    {
        return $this->bikeRideType;
    }

    public function setBikeRideType(?BikeRideType $bikeRideType): static
    {
        $this->bikeRideType = $bikeRideType;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): static
    {
        $this->survey = $survey;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
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

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function clearUsers(): static
    {
        $this->users->clear();

        return $this;
    }

    public function setRestriction(?int $restriction): static
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

    public function setLevelFilter(array $levelFilter): static
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

    public function registrationEnabled(): bool
    {
        return $this->registrationEnabled;
    }

    public function setRegistrationEnabled(bool $registrationEnabled): static
    {
        $this->registrationEnabled = $registrationEnabled;

        return $this;
    }

    /**
     * @return Collection<int, Summary>
     */
    public function getSummaries(): Collection
    {
        return $this->summaries;
    }

    public function addSummary(Summary $summary): static
    {
        if (!$this->summaries->contains($summary)) {
            $this->summaries->add($summary);
            $summary->setBikeRide($this);
        }

        return $this;
    }

    public function removeSummary(Summary $summary): static
    {
        if ($this->summaries->removeElement($summary)) {
            // set the owning side to null (unless already changed)
            if ($summary->getBikeRide() === $this) {
                $summary->setBikeRide(null);
            }
        }

        return $this;
    }

    public function isNotify(): ?bool
    {
        return $this->notify;
    }

    public function setNotify(bool $notify): static
    {
        $this->notify = $notify;

        return $this;
    }

    public function getRegistrationClosedMessage(): ?string
    {
        return $this->registrationClosedMessage;
    }

    public function setRegistrationClosedMessage(?string $registrationClosedMessage): static
    {
        $this->registrationClosedMessage = $registrationClosedMessage;

        return $this;
    }
}
