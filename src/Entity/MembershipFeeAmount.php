<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use App\Repository\MembershipFeeAmountRepository;


#[Entity(repositoryClass: MembershipFeeAmountRepository::class)]
class MembershipFeeAmount
{
    #[Column(type: "integer")]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: "float")]
    private float $amount;

    #[Column(type: "integer", nullable: true)]
    private ?int $coverage;

    #[ManyToOne(targetEntity: MembershipFee::class, inversedBy: "membershipFeeAmounts")]
    #[JoinColumn(nullable: false)]
    private MembershipFee $membershipFee;

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
