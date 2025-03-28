<?php

declare(strict_types=1);

namespace App\Entity;

use App\Form\Admin\SurveyType;
use App\Repository\SurveyRepository;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: SurveyRepository::class)]
class Survey
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Column(type: 'string', length: 50)]
    private string $title = '';

    #[Column(type: 'text')]
    private string $content;

    #[Column(type: 'datetime')]
    private DateTimeInterface $startAt;

    #[Column(type: 'datetime')]
    private DateTimeInterface $endAt;

    #[OneToMany(targetEntity: SurveyIssue::class, mappedBy: 'survey', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $surveyIssues;

    #[Column(type: 'boolean')]
    private bool $disabled = false;

    #[OneToMany(mappedBy: 'survey', targetEntity: Respondent::class)]
    private Collection $respondents;

    #[Column(type: 'boolean', options:['default' => true])]
    private bool $isAnonymous = false;

    #[OneToOne(inversedBy: 'survey', targetEntity: BikeRide::class, cascade: ['persist', 'remove'])]
    private ?BikeRide $bikeRide = null;

    #[ManyToMany(targetEntity: User::class, inversedBy: 'surveys')]
    private Collection $members;

    private ?int $restriction = SurveyType::DISPLAY_ALL_MEMBERS;

    #[Column(type: 'json', options:['default' => '[]'])]
    private array $levelFilter = [];

    public function __construct()
    {
        $this->surveyIssues = new ArrayCollection();
        $this->respondents = new ArrayCollection();
        $this->members = new ArrayCollection();
        $today = new DateTime();
        $this->startAt = $today;
        $this->endAt = $today->add(new DateInterval('P8D'));
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

    public function setStartAt(DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
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

    public function setTitle(?string $title): self
    {
        $this->title = ($title) ? $title : '';

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
     * @return Collection|Respondent[]
     */
    public function getRespondents(): Collection
    {
        return $this->respondents;
    }

    public function addRespondent(Respondent $respondent): self
    {
        if (!$this->respondents->contains($respondent)) {
            $this->respondents[] = $respondent;
            $respondent->setSurvey($this);
        }

        return $this;
    }

    public function removeRespondent(Respondent $respondent): self
    {
        if ($this->respondents->removeElement($respondent)) {
            // set the owning side to null (unless already changed)
            if ($respondent->getSurvey() === $this) {
                $respondent->setSurvey(null);
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

    public function getBikeRide(): ?BikeRide
    {
        return $this->bikeRide;
    }

    public function setBikeRide(?BikeRide $bikeRide): self
    {
        $this->bikeRide = $bikeRide;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    public function removeMembers(): self
    {
        if (!$this->members->isEmpty()) {
            foreach ($this->members as $member) {
                $this->members->removeElement($member);
            }
        }

        return $this;
    }

    public function clearMembers(): self
    {
        $this->members->clear();

        return $this;
    }

    public function setRestriction(?int $restriction): self
    {
        $this->restriction = $restriction;

        return $this;
    }

    public function getRestriction(): ?int
    {
        return $this->restriction;
    }

    public function getLevelFilter(): array
    {
        return $this->levelFilter;
    }

    public function setLevelFilter(array $levelFilter): static
    {
        $this->levelFilter = $levelFilter;

        return $this;
    }
}
