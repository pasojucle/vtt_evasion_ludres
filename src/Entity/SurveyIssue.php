<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SurveyIssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: SurveyIssueRepository::class)]
class SurveyIssue
{
    public const RESPONSE_TYPE_STRING = 1;

    public const RESPONSE_TYPE_CHOICE = 2;

    public const RESPONSE_TYPES = [
        self::RESPONSE_TYPE_STRING => 'survey.issue.string',
        self::RESPONSE_TYPE_CHOICE => 'survey.issue.choice',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: Survey::class, inversedBy: 'surveyIssues')]
    #[JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: true)]
    private ?Survey $survey;

    #[Column(type: 'string', length: 255)]
    private string $content;

    #[OneToMany(targetEntity: SurveyResponse::class, mappedBy: 'surveyIssue', cascade: ['persist', 'remove'])]
    private Collection $surveyResponses;

    #[Column(type: 'integer')]
    private int $responseType;

    public function __construct()
    {
        $this->surveyResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

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

    /**
     * @return Collection|SurveyResponse[]
     */
    public function getSurveyResponses(): Collection
    {
        return $this->surveyResponses;
    }

    public function addSurveyResponse(SurveyResponse $surveyResponse): self
    {
        if (!$this->surveyResponses->contains($surveyResponse)) {
            $this->surveyResponses[] = $surveyResponse;
            $surveyResponse->setSurveyIssue($this);
        }

        return $this;
    }

    public function removeSurveyResponse(SurveyResponse $surveyResponse): self
    {
        if ($this->surveyResponses->removeElement($surveyResponse)) {
            // set the owning side to null (unless already changed)
            if ($surveyResponse->getSurveyIssue() === $this) {
                $surveyResponse->setSurveyIssue(null);
            }
        }

        return $this;
    }

    public function getResponseType(): ?int
    {
        return $this->responseType;
    }

    public function setResponseType(int $responseType): self
    {
        $this->responseType = $responseType;

        return $this;
    }
}
