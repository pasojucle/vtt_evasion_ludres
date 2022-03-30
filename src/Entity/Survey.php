<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SurveyRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: SurveyRepository::class)]
class Survey
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 30)]
    private string $title = '';

    #[Column(type: 'text')]
    private string $content;

    #[Column(type: 'datetime')]
    private DateTime $startAt;

    #[Column(type: 'datetime')]
    private DateTime $endAt;

    #[OneToMany(targetEntity: SurveyIssue::class, mappedBy: 'survey', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $surveyIssues;

    #[Column(type: 'boolean')]
    private bool $disabled = false;

    #[OneToMany(targetEntity: SurveyUser::class, mappedBy: 'survey')]
    private Collection $surveyUsers;

    #[Column(type: 'boolean', options:['default' => true])]
    private bool $isAnonymous = true;

    public function __construct()
    {
        $this->surveyIssues = new ArrayCollection();
        $this->surveyUsers = new ArrayCollection();
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
     * @return Collection|SurveyIssue[]
     */
    public function getSurveyIssues(): Collection
    {
        return $this->surveyIssues;
    }

    public function addSurveyIssue(SurveyIssue $surveyIssue): self
    {
        if (!$this->surveyIssues->contains($surveyIssue)) {
            $this->surveyIssues[] = $surveyIssue;
            $surveyIssue->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyIssue(SurveyIssue $surveyIssue): self
    {
        if ($this->surveyIssues->removeElement($surveyIssue)) {
            // set the owning side to null (unless already changed)
            if ($surveyIssue->getSurvey() === $this) {
                $surveyIssue->setSurvey(null);
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

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return Collection|SurveyUser[]
     */
    public function getSurveyUsers(): Collection
    {
        return $this->surveyUsers;
    }

    public function addSurveyUser(SurveyUser $surveyUser): self
    {
        if (!$this->surveyUsers->contains($surveyUser)) {
            $this->surveyUsers[] = $surveyUser;
            $surveyUser->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyUser(SurveyUser $surveyUser): self
    {
        if ($this->surveyUsers->removeElement($surveyUser)) {
            // set the owning side to null (unless already changed)
            if ($surveyUser->getSurvey() === $this) {
                $surveyUser->setSurvey(null);
            }
        }

        return $this;
    }

    public function isAnonymous(): ?bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }
}
