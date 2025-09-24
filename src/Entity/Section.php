<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\SectionRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ApiResource(
    shortName: 'Section',
)]
#[GetCollection(normalizationContext: ['groups' => 'section:list'], order: ['title' => 'ASC', 'chapters.title' => 'ASC'], )]
#[Get(normalizationContext: ['groups' => 'section:item'], order: ['title' => 'ASC', 'chapters.title' => 'ASC', 'chapters.articles.title' => 'ASC'], )]

#[Patch(
    normalizationContext: ['groups' => 'section:item'],
    denormalizationContext:['groups' => 'section:write'],
    order: ['article.title' => 'ASC'],
)]
#[Delete]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:list', 'section:item', 'chapter:item', 'article:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['section:list', 'section:item', 'chapter:item', 'article:item', 'article:write', 'section:write'])]
    private ?string $title = null;

    /**
     * @var Collection<int, Chapter>
     */
    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'section', cascade: ['persist', 'remove'])]
    #[Groups(['section:list', 'section:item'])]
    private Collection $chapters;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setSection($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getSection() === $this) {
                $chapter->setSection(null);
            }
        }

        return $this;
    }
}
