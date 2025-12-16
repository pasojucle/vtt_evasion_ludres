<?php

namespace App\Entity;

use App\Entity\Enum\GardianKindEnum;
use App\Entity\Enum\KinshipEnum;
use App\Repository\UserGardianRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserGardianRepository::class)]
class UserGardian
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userGardians')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userGardians')]
    private ?Identity $identity = null;

    #[ORM\Column(type: 'Kinship')]
    private KinshipEnum $kinship = KinshipEnum::KINSHIP_FATHER;

    #[ORM\Column(type: 'GardianKind')]
    private GardianKindEnum $kind = GardianKindEnum::LEGAL_GARDIAN;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdentity(): ?Identity
    {
        return $this->identity;
    }

    public function setIdentity(?Identity $identity): static
    {
        $this->identity = $identity;

        return $this;
    }

    public function getKinship(): KinshipEnum
    {
        return $this->kinship;
    }

    public function setKinship(KinshipEnum $kinship): static
    {
        $this->kinship = $kinship;

        return $this;
    }

    public function getKind(): GardianKindEnum
    {
        return $this->kind;
    }

    public function setKind(GardianKindEnum $kind): static
    {
        $this->kind = $kind;

        return $this;
    }
}
