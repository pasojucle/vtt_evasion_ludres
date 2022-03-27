<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SurveyResponseRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: SurveyResponseRepository::class)]
class SurveyResponse
{
    public const VALUE_NO = 0;

    public const VALUE_YES = 1;

    public const VALUE_NO_OPINION = 2;

    public const VALUES = [
        self::VALUE_NO => 'survey.response.no',
        self::VALUE_YES => 'survey.response.yes',
        self::VALUE_NO_OPINION => 'survey.response.no_opinion',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: SurveyIssue::class, inversedBy: 'surveyResponses')]
    #[JoinColumn(nullable: false)]
    private $surveyIssue;

    #[Column(type: 'text', nullable: true)]
    private ?string $value;

    #[Column(type: 'string', length: 23)]
    private string $uuid;

    #[ManyToOne(targetEntity: User::class)]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurveyIssue(): ?SurveyIssue
    {
        return $this->surveyIssue;
    }

    public function setSurveyIssue(?SurveyIssue $surveyIssue): self
    {
        $this->surveyIssue = $surveyIssue;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
