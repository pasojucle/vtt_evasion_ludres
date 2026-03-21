<?php

namespace App\Entity;

use App\Repository\BoardRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoardRoleRepository::class)]
class BoardRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 50)]
    private string $name = '';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $board = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $orderBy = null;

    #[ORM\OneToMany(mappedBy: 'boardRole', targetEntity: Member::class)]
    private Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isBoard(): bool
    {
        return $this->board;
    }

    public function setBoard(bool $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(?int $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addUser(Member $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setBoardRole($this);
        }

        return $this;
    }

    public function removeUser(Member $member): self
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getBoardRole() === $this) {
                $member->setBoardRole(null);
            }
        }

        return $this;
    }
}
