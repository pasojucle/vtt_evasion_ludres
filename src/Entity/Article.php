<?php

namespace App\Entity;

use App\Entity\Chapter;
use App\Entity\Section;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleRepository;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Chapter::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\OrderBy({"title" = "ASC"})
     */
    private $chapter;

    private $chapterTitle;
    private $section;
    private $sectionTitle;
    private $encryption;

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

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(?Chapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getChapterTitle(): ?string
    {
        return $this->chapterTitle;
    }

    public function setChapterTitle(string $chapterTitle): self
    {
        $this->chapterTitle = $chapterTitle;

        return $this;
    }


    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getSectionTitle(): ?string
    {
        return $this->sectionTitle;
    }

    public function setSectionTitle(string $sectionTitle): self
    {
        $this->sectionTitle = $sectionTitle;

        return $this;
    }
 
    public function getEncryption(): bool
    {
        return $this->encryption;
    }

 
    public function setEncryption(bool $encryption): self
    {
        $this->encryption = $encryption;

        return $this;
    }
}
