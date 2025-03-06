<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\ContentKindEnum;
use App\Repository\ContentRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
class Content
{
    public const ROUTES = [
        'home' => 'content.route.home',
        'registration_detail' => 'content.route.registration_detail',
        'club' => 'content.route.club',
        'school_practices' => 'content.route.school_practices',
        'school_overview' => 'content.route.school_overview',
        'school_operating' => 'content.route.school_operating',
        'school_equipment' => 'content.route.school_equipment',
        'school_documentation' => 'content.route.school_documentation',
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
        'user_change_infos' => 'content.route.user_change_infos',
        'second_hand' => 'content.route.second_hand',
        'second_hand_contact' => 'content.route.second_hand_contact',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 100)]
    private string $route;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $startAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $endAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $orderBy = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ? string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $buttonLabel = null;

    /**
     * @var ArrayCollection <Background>
     */
    #[ORM\ManyToMany(targetEntity: Background::class, inversedBy: 'contents')]
    private Collection $backgrounds;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'contents')]
    private $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[ORM\OrderBy(['orderBy' => 'ASC'])]
    private $contents;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $parameters = null;

    #[ORM\Column(type: 'ContentKind')]
    private ?object $kind = ContentKindEnum::BACKROUND_AND_TEXT;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $youtubeEmbed = null;

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

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeInterface $endAt): self
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

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getKind(): ?object
    {
        return $this->kind;
    }

    public function setKind(object $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getYoutubeEmbed(): ?string
    {
        return $this->youtubeEmbed;
    }

    public function setYoutubeEmbed(?string $youtubeEmbed): static
    {
        $this->youtubeEmbed = $youtubeEmbed;

        return $this;
    }
}
