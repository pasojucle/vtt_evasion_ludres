<?php

namespace App\Entity;

use App\Repository\MemberPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberPermissionRepository::class)]
class MemberPermission
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'memberPermissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\Id]
    #[ORM\Column(type: 'Permission')]
    private ?object $permission = null;

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getPermission(): ?object
    {
        return $this->permission;
    }

    public function setPermission(object $permission): static
    {
        $this->permission = $permission;

        return $this;
    }
}
