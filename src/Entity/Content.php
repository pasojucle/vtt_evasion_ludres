<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;

#[Entity(repositoryClass: ContentRepository::class)]
class Content
{
    public const IS_FLASH = [
        true => 'content.type.flash',
        false => 'content.type.content',
    ];

    public const ROUTES = [
        'home' => 'content.route.home',
        'registration_detail' => 'content.route.registration_detail',
        'club' => 'content.route.club',
        'school_practices' => 'content.route.school_practices',
        'school_overview' => 'content.route.school_overview',
        'school_operating' => 'content.route.school_operating',
        'school_equipment' => 'content.route.school_equipment',
        'contact' => 'content.route.contact',
        'rules' => 'content.route.rules',
        'legal_notices' => 'content.route.legal_notices',
        'login_help' => 'content.route.login_help',
        'registration_membership_fee' => 'content.route.registration_membership_fee',
        'registration_tuto' => 'content.route.registration_tuto',
        'schedule' => 'content.route.schedule',
        'user_account' => 'content.route.user_account',
        'default' => 'content.route.default',
        'links' => 'content.route.links',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 100)]
    private string $route;

    #[Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTime $startAt;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTime $endAt;

    #[Column(type: 'integer', nullable: true)]
    private ?int $orderBy = null;

    #[Column(type: 'boolean')]
    private bool $isActive = true;

    #[Column(type: 'boolean')]
    private bool $isFlash = false;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ? string $title = null;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $url = null;

    #[Column(type: 'string', length: 30, nullable: true)]
    private ?string $buttonLabel = null;

    #[ManyToMany(targetEntity: Background::class, inversedBy: 'contents')]
    private Collection $backgrounds;

    #[ManyToOne(targetEntity: self::class, inversedBy: 'contents')]
    private $parent = null;

    #[OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[OrderBy(['orderBy' => 'ASC'])]
    private $contents;

    #[Column(type: 'boolean', options: ['default' => 0])]
    private bool $backgroundOnly = false;

    public function __construct()
    {
        $this->backgrounds = new ArrayCollection();
        $this->contents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

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

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(?int $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function IsFlash(): ?bool
    {
        return $this->isFlash;
    }

    public function setIsFlash(bool $isFlash): self
    {
        $this->isFlash = $isFlash;

        return $this;
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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getButtonLabel(): ?string
    {
        return $this->buttonLabel;
    }

    public function setButtonLabel(?string $buttonLabel): self
    {
        $this->buttonLabel = $buttonLabel;

        return $this;
    }

    /**
     * @return Collection<int, BackgroundImage>
     */
    public function getBackgrounds(): Collection
    {
        return $this->backgrounds;
    }

    public function addBackground(Background $background): self
    {
        if (!$this->backgrounds->contains($background)) {
            $this->backgrounds[] = $background;
        }

        return $this;
    }

    public function removeBackground(Background $background): self
    {
        $this->backgrounds->removeElement($background);

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(self $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setParent($this);
        }

        return $this;
    }

    public function removeContent(self $content): self
    {
        if ($this->contents->removeElement($content)) {
            // set the owning side to null (unless already changed)
            if ($content->getParent() === $this) {
                $content->setParent(null);
            }
        }

        return $this;
    }

    public function isBackgroundOnly(): ?bool
    {
        return $this->backgroundOnly;
    }

    public function setBackgroundOnly(bool $backgroundOnly): self
    {
        $this->backgroundOnly = $backgroundOnly;

        return $this;
    }
}
