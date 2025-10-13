<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Article;
use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[ApiResource(
    shortName: 'Chapter',
)]
#[Get(
    normalizationContext: ['groups' => 'chapter:item'],
    order: ['article.title' => 'ASC'],
)]
#[GetCollection(
    normalizationContext: ['groups' => 'chapter:list'],
    filters: ['chapter.search_filter'],
    parameters: [
        'section.id' => new QueryParameter(filter: 'chapter.search_filter')
    ]
)]
#[Patch(
    normalizationContext: ['groups' => 'chapter:item'],
    denormalizationContext: ['groups' => 'chapter:write'],
    order: ['article.title' => 'ASC'],
)]
#[Delete]
class Chapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:list', 'section:item', 'chapter:item', 'chapter:list', 'article:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['section:list', 'section:item', 'chapter:item', 'chapter:list', 'article:item', 'article:write', 'chapter:write'])]
    private ?string $title = 'undefined';

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[Groups(['chapter:item', 'article:item'])]
    private ?Section $section = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'chapter', cascade: ['persist', 'remove'])]
    #[Groups(['section:item', 'chapter:item'])]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
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

    public function openArticleSheet(Article $article): static
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
