<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MembershipFeeAmountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MembershipFeeAmountRepository::class)
 */
class MembershipFeeAmount
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coverage;

    /**
     * @ORM\ManyToOne(targetEntity=MembershipFee::class, inversedBy="membershipFeeAmounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $membershipFee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCoverage(): ?int
    {
        return $this->coverage;
    }

    public function setCoverage(int $coverage): self
    {
        $this->coverage = $coverage;

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
}
