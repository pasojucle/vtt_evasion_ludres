<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
class Level
{
    public const TYPE_SCHOOL_MEMBER = 1;

    public const TYPE_FRAME = 2;

    public const TYPE_ALL_MEMBER = 'ALL_MEMBER ';

    public const TYPE_ALL_FRAME = 'ALL_FRAME';

    public const TYPE_BOARD_MEMBER = 'BOARD_MEMBER';

    public const TYPE_ADULT_MEMBER = 3;

    public const TYPES = [
        self::TYPE_SCHOOL_MEMBER => 'level.type.school_member',
        self::TYPE_FRAME => 'level.type.frame',
        self::TYPE_ADULT_MEMBER => 'level.type.adult_member',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'level')]
    private Collection $users;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $color;

    #[ORM\OneToMany(targetEntity: Cluster::class, mappedBy: 'level')]
    private Collection $clusters;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $orderBy = null;

    #[ORM\Column(type: 'integer')]
    private int $type;

    #[ORM\Column(type: 'boolean')]
    private bool $isProtected = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isDeleted = false;

    #[ORM\OneToMany(mappedBy: 'level', targetEntity: Indemnity::class)]
    private $indemnities;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $accompanyingCertificat = false;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\OneToMany(targetEntity: Skill::class, mappedBy: 'level')]
    private Collection $skills;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->clusters = new ArrayCollection();
        $this->indemnities = new ArrayCollection();
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

    public function setOrderBy(?int $orderBy): self
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

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Collection|Indemnity[]
     */
    public function getIndemnities(): Collection
    {
        return $this->indemnities;
    }

    public function addIndemnity(Indemnity $indemnity): self
    {
        if (!$this->indemnities->contains($indemnity)) {
            $this->indemnities[] = $indemnity;
            $indemnity->setLevel($this);
        }

        return $this;
    }

    public function removeIndemnity(Indemnity $indemnity): self
    {
        if ($this->indemnities->removeElement($indemnity)) {
            // set the owning side to null (unless already changed)
            if ($indemnity->getLevel() === $this) {
                $indemnity->setLevel(null);
            }
        }

        return $this;
    }

    public function isAccompanyingCertificat(): bool
    {
        return $this->accompanyingCertificat;
    }

    public function setAccompanyingCertificat(bool $accompanyingCertificat): self
    {
        $this->accompanyingCertificat = $accompanyingCertificat;

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
            $skill->setLevel($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            // set the owning side to null (unless already changed)
            if ($skill->getLevel() === $this) {
                $skill->setLevel(null);
            }
        }

        return $this;
    }
}
