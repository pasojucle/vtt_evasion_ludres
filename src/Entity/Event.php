<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventRepository;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    public const PERIOD_DAY = 'jour';

    public const PERIOD_WEEK = 'semaine';

    public const PERIOD_MONTH = 'mois';

    public const PERIOD_NEXT = 'prochainement';

    public const PERIOD_ALL = 'tous';

    public const DIRECTION_PREV = 1;

    public const DIRECTION_NEXT = 2;

    public const PERIODS = [
        self::PERIOD_DAY => 'event.period.day',
        self::PERIOD_WEEK => 'event.period.week',
        self::PERIOD_MONTH => 'event.period.month',
        self::PERIOD_NEXT => 'event.period.next',
        self::PERIOD_ALL => 'event.period.all',
    ];

    public const TYPE_CASUAL = 1;

    public const TYPE_SCHOOL = 2;

    public const TYPE_ADULT = 3;

    public const TYPE_HOLIDAYS = 4;

    public const TYPES = [
        self::TYPE_CASUAL => 'event.type.casual',
        self::TYPE_SCHOOL => 'event.type.school',
        self::TYPE_ADULT => 'event.type.adult',
        self::TYPE_HOLIDAYS => 'event.type.holidays',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $displayDuration = 8;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minAge;

    /**
     * @ORM\OneToMany(targetEntity=Cluster::class, mappedBy="event")
     */
    private $clusters;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = self::TYPE_CASUAL;

    /**
     * @ORM\Column(type="integer", options={"default":1})
     */
    private $closingDuration = 1;

    public function __construct()
    {
        $this->clusters = new ArrayCollection();
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

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
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
            $cluster->setEvent($this);
        }

        return $this;
    }

    public function removeCluster(Cluster $cluster): self
    {
        if ($this->clusters->removeElement($cluster)) {
            // set the owning side to null (unless already changed)
            if ($cluster->getEvent() === $this) {
                $cluster->setEvent(null);
            }
        }

        return $this;
    }

    public function isRegistrable(): bool
    {
        if (self::TYPE_HOLIDAYS === $this->type) {
            return false;
        }

        $today = new DateTime();
        $intervalDisplay = new DateInterval('P' . $this->displayDuration . 'D');
        $intervalClosing = new DateInterval('P' . $this->closingDuration . 'D');
        $displayAt = DateTime::createFromFormat('Y-m-d H:i:s', $this->startAt->format('Y-m-d') . ' 00:00:00');
        $closingAt = DateTime::createFromFormat('Y-m-d H:i:s', $this->startAt->format('Y-m-d') . ' 23:59:59');

        return $displayAt->sub($intervalDisplay) <= $today && $today <= $closingAt->sub($intervalClosing);
    }

    public function getAccessAvailabity(?User $user): bool
    {
        if (self::TYPE_HOLIDAYS === $this->type) {
            return false;
        }

        $today = new DateTime();
        $today = DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' 00:00:00');

        $level = (null !== $user) ? $user->getLevel() : null;
        $type = (null !== $level) ? $level->getType() : null;

        return Level::TYPE_FRAME === $type && self::TYPE_SCHOOL === $this->type && $today <= $this->startAt;
    }

    public function isOver(): bool
    {
        $today = new DateTime();
        $today = DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' 00:00:00');

        return $this->startAt < $today;
    }

    public function isNext(): bool
    {
        $today = new DateTime();
        $today = DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' 00:00:00');
        $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $this->startAt->format('Y-m-d') . ' 23:59:59');
        $displayAt = DateTime::createFromFormat('Y-m-d H:i:s', $this->startAt->format('Y-m-d') . ' 00:00:00');
        $interval = new DateInterval('P' . $this->displayDuration . 'D');

        return $displayAt->sub($interval) <= $today && $today <= $startAt;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getClosingDuration(): ?int
    {
        return $this->closingDuration;
    }

    public function setClosingDuration(?int $closingDuration): self
    {
        $this->closingDuration = $closingDuration;

        return $this;
    }
}
