<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MembershipFeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass=MembershipFeeRepository::class)
 */
class MembershipFee
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $additionalFamilyMember;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $newMember;

    /**
     * @ORM\OneToMany(targetEntity=MembershipFeeAmount::class, mappedBy="membershipFee")
     * @OrderBy({"coverage" = "ASC"})
     */
    private $membershipFeeAmounts;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    public function __construct()
    {
        $this->membershipFeeAmounts = new ArrayCollection();
    }

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

    /**
     * @return Collection|MembershipFeeAmount[]
     */
    public function getMembershipFeeAmounts(): Collection
    {
        return $this->membershipFeeAmounts;
    }

    public function addMembershipFeeAmount(MembershipFeeAmount $membershipFeeAmount): self
    {
        if (! $this->membershipFeeAmounts->contains($membershipFeeAmount)) {
            $this->membershipFeeAmounts[] = $membershipFeeAmount;
            $membershipFeeAmount->setMembershipFee($this);
        }

        return $this;
    }

    public function removeMembershipFeeAmount(MembershipFeeAmount $membershipFeeAmount): self
    {
        if ($this->membershipFeeAmounts->removeElement($membershipFeeAmount)) {
            // set the owning side to null (unless already changed)
            if ($membershipFeeAmount->getMembershipFee() === $this) {
                $membershipFeeAmount->setMembershipFee(null);
            }
        }

        return $this;
    }

    public function getAdditionalFamilyMember(): ?bool
    {
        return $this->additionalFamilyMember;
    }

    public function setAdditionalFamilyMember(bool $additionalFamilyMember): self
    {
        $this->additionalFamilyMember = $additionalFamilyMember;

        return $this;
    }

    public function getNewMember(): ?bool
    {
        return $this->newMember;
    }

    public function setNewMember(bool $newMember): self
    {
        $this->newMember = $newMember;

        return $this;
    }

    public function getMembershipFee(): ?MembershipFee
    {
        return $this->membershipFee;
    }

    public function setMembershipFee(?MembershipFee $membershipFee): self
    {
        $this->membershipFee = $membershipFee;

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
}
