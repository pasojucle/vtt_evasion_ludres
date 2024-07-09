<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\HistoryRepository;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'registrationChanges')]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $entity = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\Column]
    private array $value = [];

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options:['default' => false])]
    private ?bool $notify = false;

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

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function setValue(array $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isNotify(): bool
    {
        return $this->notify;
    }

    public function setNotify(bool $notify): static
    {
        $this->notify = $notify;

        return $this;
    }
}
