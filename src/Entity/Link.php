<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LinkRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    public const POSITION_LINK_PAGE = 1;

    public const POSITION_HOME_FOOTER = 2;

    public const POSITION_HOME_BIKE_RIDE = 3;

    public const POSITIONS = [
        self::POSITION_LINK_PAGE => 'link.position.link_page',
        self::POSITION_HOME_FOOTER => 'link.position.home_footer',
        self::POSITION_HOME_BIKE_RIDE => 'link.position.home_bike_ride',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image;

    #[ORM\Column(type: 'integer', options:['default' => 1])]
    private int $position = 1;

    #[ORM\Column(type: 'integer')]
    private int $orderBy = -1;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updateAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }
}
