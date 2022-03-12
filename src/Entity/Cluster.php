<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClusterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: ClusterRepository::class)]
class Cluster
{
    public const SCHOOL_MAX_MEMEBERS = 6;

    public const CLUSTER_FRAME = 'Encadrement';

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 100)]
    private string $title;

    #[OneToMany(targetEntity: Session::class, mappedBy: 'cluster')]
    private Collection $sessions;

    #[ManyToOne(targetEntity: BikeRide::class, inversedBy: 'clusters')]
    #[JoinColumn(nullable: false)]
    private BikeRide $bikeRide;

    #[Column(type: 'integer', nullable: true)]
    private ?int $maxUsers;

    #[ManyToOne(targetEntity: Level::class, inversedBy: 'clusters')]
    private Level $level;

    #[Column(type: 'string', length: 25, nullable: true)]
    private ?string $role;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $isComplete = false;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
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

    /**     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setCluster($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getCluster() === $this) {
                $session->setCluster(null);
            }
        }

        return $this;
    }

    public function getMemberSessions(): ArrayCollection
    {
        $memberSessions = [];
        foreach ($this->sessions as $session) {
            $roles = $session->getUser()->getRoles();
            if (in_array('USER', $roles, true)) {
                $memberSessions[] = $session->getUser();
            }
        }

        return new ArrayCollection($memberSessions);
    }

    public function getBikeRide(): ?BikeRide
    {
        return $this->bikeRide;
    }

    public function setBikeRide(?BikeRide $bikeRide): self
    {
        $this->bikeRide = $bikeRide;

        return $this;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(?int $maxUsers): self
    {
        $this->maxUsers = $maxUsers;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getSessionAvailable(): ArrayCollection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq('availability', Session::AVAILABILITY_UNAVAILABLE))
        ;
        $sessions = $this->sessions->matching($criteria);

        $sortedSessions = [];
        if (!$sessions->isEmpty()) {
            $sortedSessions = $sessions->toArray();
            usort($sortedSessions, function ($a, $b) {
                $a = strtolower($a->getUser()->getFirstIdentity()->getName());
                $b = strtolower($b->getUser()->getFirstIdentity()->getName());

                if ($a === $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            });
        }

        return new ArrayCollection($sortedSessions);
    }

    public function isComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function setIsComplete(bool $isComplete): self
    {
        $this->isComplete = $isComplete;

        return $this;
    }
}
