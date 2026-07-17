<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use App\Entity\User;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait DisableableTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $disabledAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'disabled_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $disabledBy = null;
    
    public function getDisabledAt(): ?DateTimeInterface
    {
        return $this->disabledAt;
    }

    public function setDisabledAt(?DateTimeInterface $disabledAt): self
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    public function getDisabledBy(): ?User
    {
        return $this->disabledBy;
    }

    public function setDisabledBy(?User $disabledBy): self
    {
        $this->disabledBy = $disabledBy;

        return $this;
    }


    public function isDisabled(): bool
    {
        return null !== $this->disabledAt;
    }
}
