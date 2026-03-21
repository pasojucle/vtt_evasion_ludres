<?php

namespace App\Entity;

use App\Entity\Enum\EvaluationEnum;
use App\Repository\MemberSkillRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberSkillRepository::class)]
class MemberSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $evaluateAt = null;

    #[ORM\Column(type: 'Evaluation')]
    private EvaluationEnum $evaluation = EvaluationEnum::UNACQUIRED;

    #[ORM\ManyToOne(inversedBy: 'memberSkills')]
    private ?Member $member = null;

    #[ORM\ManyToOne(inversedBy: 'memberSkills')]
    private ?Skill $skill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluateAt(): ?DateTimeImmutable
    {
        return $this->evaluateAt;
    }

    public function setEvaluateAt(?DateTimeImmutable $evaluateAt): static
    {
        $this->evaluateAt = $evaluateAt;

        return $this;
    }

    public function getEvaluation(): EvaluationEnum
    {
        return $this->evaluation;
    }

    public function setEvaluation(EvaluationEnum $evaluation): static
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): static
    {
        $this->skill = $skill;

        return $this;
    }
}
