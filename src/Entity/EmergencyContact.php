<?php

namespace App\Entity;

use App\Repository\EmergencyContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmergencyContactRepository::class)]
class EmergencyContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 14)]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $kinship = null;

    #[ORM\OneToOne(inversedBy: 'emergencyContact', cascade: ['persist', 'remove'])]
    private ?Member $member = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getKinship(): ?string
    {
        return $this->kinship;
    }

    public function setKinship(string $kinship): static
    {
        $this->kinship = $kinship;

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
}
