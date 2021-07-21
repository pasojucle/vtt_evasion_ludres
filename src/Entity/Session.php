<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SessionRepository::class)
 */
class Session
{
    public const AVAILABILITY_PRESENT = 1;
    public const AVAILABILITY_AVAILABLE = 2;
    public const AVAILABILITY_UNAVAILABLE = 3;

    public const AVAILABILITIES = [
        self::AVAILABILITY_PRESENT => 'session.availability.present',
        self::AVAILABILITY_AVAILABLE => 'session.availability.available',
        self::AVAILABILITY_UNAVAILABLE => 'session.availability.unavailable',
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sessions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Cluster::class, inversedBy="sessions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cluster;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPresent = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $availability;

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
