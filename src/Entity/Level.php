<?php

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LevelRepository::class)
 */
class Level
{
    public const TYPE_MEMBER = 1;
    public const TYPE_FRAME = 2;
    public const TYPE_ALL_MEMBER = 'ALL_MEMBER ';
    public const TYPE_ALL_FRAME = 'ALL_FRAME';
    public const TYPE_ADULT = 'ADULT';

    public const TYPES = [
        self::TYPE_MEMBER => 'level.type.member',
        self::TYPE_FRAME => 'level.type.frame',
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

        /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="level")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $color;


    /**
     * @ORM\OneToMany(targetEntity=Cluster::class, mappedBy="level")
     */
    private $clusters;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderBy;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isProtected = false;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->clusters = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setLevel($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getLevel() === $this) {
                $user->setLevel(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|Cluster[]
     */
    public function getClusters(): Collection
    {
        return $this->clusters;
    }

    public function addCluster(Cluster $cluster): self
    {
        if (!$this->clusters->contains($cluster)) {
            $this->clusters[] = $cluster;
            $cluster->setLevel($this);
        }

        return $this;
    }

    public function removeCluster(Cluster $cluster): self
    {
        if ($this->clusters->removeElement($cluster)) {
            // set the owning side to null (unless already changed)
            if ($cluster->getLevel() === $this) {
                $cluster->setLevel(null);
            }
        }

        return $this;
    }

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(int $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
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

    public function getIsProtected(): ?bool
    {
        return $this->isProtected;
    }

    public function setIsProtected(bool $isProtected): self
    {
        $this->isProtected = $isProtected;

        return $this;
    }
}
