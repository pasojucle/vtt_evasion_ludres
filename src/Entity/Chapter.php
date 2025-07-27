<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Article;
use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[ApiResource(
    shortName: 'Chapter',
)]
#[Get(
    normalizationContext: ['groups' => 'Chapter:item'],
    order: ['article.title' => 'ASC'],
)]
#[GetCollection(
    normalizationContext: ['groups' => 'Chapter:list'],
    filters: ['chapter.search_filter'],
    parameters: [
        'section.id' => new QueryParameter(filter: 'chapter.search_filter')
    ]
)]
class Chapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:list', 'section:item', 'Chapter:item', 'Chapter:list', 'Article:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['section:list', 'section:item', 'Chapter:item', 'Chapter:list', 'Article:item', 'Article:write'])]
    private string $title = 'undefined';

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[Groups(['Chapter:item', 'Article:item'])]
    private ?Section $section = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'chapter')]
    #[Groups(['section:item', 'Chapter:item'])]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

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

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): static
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setChapter($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getChapter() === $this) {
                $article->setChapter(null);
            }
        }

        return $this;
    }
}
