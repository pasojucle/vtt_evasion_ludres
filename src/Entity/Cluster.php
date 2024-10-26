<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClusterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClusterRepository::class)]
class Cluster
{
    public const SCHOOL_MAX_MEMBERS = 6;

    public const CLUSTER_FRAME = 'Encadrement';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $title = '';

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'cluster')]
    private Collection $sessions;

    #[ORM\ManyToOne(targetEntity: BikeRide::class, inversedBy: 'clusters')]
    #[ORM\JoinColumn(nullable: false)]
    private BikeRide $bikeRide;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxUsers;

    #[ORM\ManyToOne(targetEntity: Level::class, inversedBy: 'clusters')]
    private ?Level $level = null;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private ?string $role;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isComplete = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $position = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class, inversedBy: 'clusters')]
    private Collection $skills;


    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->skills = new ArrayCollection();
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

    /**
    * * @return Collection|Session[]
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

    public function isComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function setIsComplete(bool $isComplete): self
    {
        $this->isComplete = $isComplete;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }
}
