<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SkillCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    /**
     * @var Collection<int, Cluster>
     */
    #[ORM\ManyToMany(targetEntity: Cluster::class, mappedBy: 'skills')]
    private Collection $clusters;

    /**
     * @var Collection<int, MemberSkill>
     */
    #[ORM\OneToMany(targetEntity: MemberSkill::class, mappedBy: 'skill', cascade: ['remove'])]
    private Collection $memberSkills;

    public function __construct()
    {
        $this->clusters = new ArrayCollection();
        $this->memberSkills = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCategory(): ?SkillCategory
    {
        return $this->category;
    }

    public function setCategory(?SkillCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, Cluster>
     */
    public function getClusters(): Collection
    {
        return $this->clusters;
    }

    public function addCluster(Cluster $cluster): static
    {
        if (!$this->clusters->contains($cluster)) {
            $this->clusters->add($cluster);
            $cluster->addSkill($this);
        }

        return $this;
    }

    public function removeCluster(Cluster $cluster): static
    {
        if ($this->clusters->removeElement($cluster)) {
            $cluster->removeSkill($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, MemberSkill>
     */
    public function getMemberkills(): Collection
    {
        return $this->memberSkills;
    }

    public function addMemberSkill(MemberSkill $memberSkill): static
    {
        if (!$this->memberSkills->contains($memberSkill)) {
            $this->memberSkills->add($memberSkill);
            $memberSkill->setSkill($this);
        }

        return $this;
    }

    public function removeMemberSkill(MemberSkill $memberSkill): static
    {
        if ($this->memberSkills->removeElement($memberSkill)) {
            // set the owning side to null (unless already changed)
            if ($memberSkill->getSkill() === $this) {
                $memberSkill->setSkill(null);
            }
        }

        return $this;
    }
}
