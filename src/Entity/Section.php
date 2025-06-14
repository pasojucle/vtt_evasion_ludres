<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ApiResource(
    shortName: 'Section',
)]
#[GetCollection(normalizationContext: ['groups' => 'section:list'], order: ['title' => 'ASC', 'chapters.title' => 'ASC'], )]
#[Get(normalizationContext: ['groups' => 'section:item'], order: ['title' => 'ASC', 'chapters.title' => 'ASC', 'chapters.articles.title' => 'ASC'], )]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:list', 'Chapter:item', 'Article:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['section:list', 'section:item', 'Chapter:item', 'Article:item'])]
    private ?string $title = null;

    /**
     * @var Collection<int, Chapter>
     */
    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'section')]
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
