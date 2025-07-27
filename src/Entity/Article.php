<?php

namespace App\Entity;


use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\State\ArticleStateProcessor;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticleRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ApiResource(
    shortName: 'Article',
)]
#[Get(
    normalizationContext: ['groups' => 'Article:item'],
    security: "is_granted('ROLE_USER')"
)]
#[Post(
    denormalizationContext: ['groups' => 'Article:write'],
    processor: ArticleStateProcessor::class,
    security: "is_granted('ROLE_USER')"
)]
#[Delete(
    security: "is_granted('ROLE_USER')"
)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:item', 'Chapter:item', 'Article:item', 'Article:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['section:item', 'Chapter:item', 'Article:item', 'Article:write'])]
    private string $title = 'undefined';

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['Chapter:item', 'Article:item', 'Article:write'])]
    private string $content = 'undefined';

    #[ORM\ManyToOne(inversedBy: 'articles', cascade: ['persist'])]
    #[Groups(['Article:item','Article:write'])]
    private ?Chapter $chapter = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    private ?User $user = null;

    #[Groups(['Article:write'])]
    private Section $section;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(?Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function setSection($section): static
    {
        $this->section = $section;

        return $this;
    }
}
