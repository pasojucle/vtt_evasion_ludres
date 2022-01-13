<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VoteRepository::class)
 */
class Vote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endAt;

    /**
     * @ORM\OneToMany(targetEntity=VoteIssue::class, mappedBy="vote", cascade={"persist"})
     */
    private $voteIssues;

    /**
     * @ORM\Column(type="boolean")
     */
    private $disabled = 0;

    public function __construct()
    {
        $this->voteIssues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return Collection|VoteIssue[]
     */
    public function getVoteIssues(): Collection
    {
        return $this->voteIssues;
    }

    public function addVoteIssue(VoteIssue $voteIssue): self
    {
        if (!$this->voteIssues->contains($voteIssue)) {
            $this->voteIssues[] = $voteIssue;
            $voteIssue->setVote($this);
        }

        return $this;
    }

    public function removeVoteIssue(VoteIssue $voteIssue): self
    {
        if ($this->voteIssues->removeElement($voteIssue)) {
            // set the owning side to null (unless already changed)
            if ($voteIssue->getVote() === $this) {
                $voteIssue->setVote(null);
            }
        }

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

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }
}
