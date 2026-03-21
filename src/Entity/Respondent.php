<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RespondentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespondentRepository::class)]
class Respondent
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'respondents')]
    #[ORM\JoinColumn(nullable: false)]
    private Survey $survey;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'respondents')]
    #[ORM\JoinColumn(nullable: false)]
    private Member $member;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    public function geId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
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

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }
}
