<?php

namespace App\Entity;


use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ApiResource(
    shortName: 'Article',
)]
#[Get(
    normalizationContext: ['groups' => 'Article:item'],
    security: "is_granted('ROLE_USER')"
)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:item', 'Chapter:item', 'Article:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['section:item', 'Chapter:item', 'Article:item'])]
    private string $title = 'undefined';

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['Chapter:item', 'Article:item'])]
    private string $content = 'undefined';

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups(['Article:item', ])]
    private ?Chapter $chapter = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    private ?User $user = null;

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
}
