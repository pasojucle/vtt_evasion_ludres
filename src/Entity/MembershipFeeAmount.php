<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\LicenceCoverageEnum;
use App\Repository\MembershipFeeAmountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembershipFeeAmountRepository::class)]
class MembershipFeeAmount
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\Column(type: 'string', enumType: LicenceCoverageEnum::class, options:['default' => LicenceCoverageEnum::UNDEFINED->value])]
    private LicenceCoverageEnum $coverage = LicenceCoverageEnum::UNDEFINED;

    #[ORM\ManyToOne(targetEntity: MembershipFee::class, inversedBy: 'membershipFeeAmounts')]
    #[ORM\JoinColumn(nullable: false)]
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

    public function getCoverage(): LicenceCoverageEnum
    {
        return $this->coverage;
    }

    public function setCoverage(LicenceCoverageEnum $coverage): self
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
