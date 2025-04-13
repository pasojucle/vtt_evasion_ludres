<?php

namespace App\Entity;

use App\Repository\UserPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPermissionRepository::class)]
class UserPermission
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'userPermissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\Column(type: 'Permission')]
    private ?object $permission = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
