<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClusterRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ClusterRepository::class)
 */
class Cluster
{
    public const SCHOOL_MAX_MEMEBERS = 6;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Session::class, mappedBy="cluster")
     */
    private $sessions;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="clusters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxUsers;

    /**
     * @ORM\ManyToOne(targetEntity=Level::class, inversedBy="clusters")
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $role;

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
            if (in_array('USER', $roles)) {
                $memberSessions[] = $session->getUser();
            }
        }

        return new ArrayCollection($memberSessions);
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

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
}
