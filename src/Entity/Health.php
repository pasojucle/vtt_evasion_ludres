<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Repository\HealthRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: HealthRepository::class)]
class Health
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $medicalCertificateDate = null;

    #[OneToOne(targetEntity: User::class, mappedBy: 'health')]
    private ?User $user;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    private array $swornCertifications = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedicalCertificateDate(): ?DateTimeInterface
    {
        return $this->medicalCertificateDate;
    }

    public function setMedicalCertificateDate(?DateTimeInterface $medicalCertificateDate): self
    {
        $this->medicalCertificateDate = $medicalCertificateDate;

        return $this;
    }

    public function getSwornCertifications(): array
    {
        return $this->swornCertifications;
    }

    public function setSwornCertifications(array $swornCertifications): static
    {
        $this->swornCertifications = $swornCertifications;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
