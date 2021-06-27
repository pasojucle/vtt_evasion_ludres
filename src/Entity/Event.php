<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    public const PERIOD_DAY = 'jour';
    public const PERIOD_WEEK = 'semaine';
    public const PERIOD_MONTH = 'mois';
    public const PERIOD_ALL = 'tous';

    public const DIRECTION_PREV = 1;
    public const DIRECTION_NEXT = 2;

    public const PERIODS = [
        self::PERIOD_DAY => 'event.period.day',
        self::PERIOD_WEEK=> 'event.period.week',
        self::PERIOD_MONTH => 'event.period.month',
        self::PERIOD_ALL => 'event.period.all',
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
    private $displayDuration;

    /**
     * @ORM\Column(type="datetime")
     */
    private $closingAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minAge;

    /**
     * @ORM\OneToMany(targetEntity=Session::class, mappedBy="event")
     */
    private $sessions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $usersPerCluster;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
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

    public function setStartAt(\DateTimeInterface $startAt): self
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

    public function setDisplayDuration(int $displayDuration): self
    {
        $this->displayDuration = $displayDuration;

        return $this;
    }

    public function getClosingAt(): ?\DateTimeInterface
    {
        return $this->closingAt;
    }

    public function setClosingAt(\DateTimeInterface $closingAt): self
    {
        $this->closingAt = $closingAt;

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
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setEvent($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getEvent() === $this) {
                $session->setEvent(null);
            }
        }

        return $this;
    }

    public function getUsersPerCluster(): ?int
    {
        return $this->usersPerCluster;
    }

    public function setUsersPerCluster(?int $usersPerCluster): self
    {
        $this->usersPerCluster = $usersPerCluster;

        return $this;
    }
}
