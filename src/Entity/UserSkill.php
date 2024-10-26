<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Enum\EvaluationEnum;
use App\Repository\UserSkillRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: UserSkillRepository::class)]
class UserSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $evaluateAt = null;

    #[ORM\Column(type: 'Evaluation')]
    private EvaluationEnum $evaluation = EvaluationEnum::UNACQUIRED;

    #[ORM\ManyToOne(inversedBy: 'userSkills')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userSkills')]
    private ?Skill $skill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluateAt(): DateTimeImmutable
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
