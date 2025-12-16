<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Repository\HealthRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HealthRepository::class)]
class Health
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $medicalCertificateDate = null;
    
    #[ORM\OneToOne(inversedBy: 'health', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    private array $consents = [];

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

    public function getConsents(): array
    {
        return $this->consents;
    }

    public function setConsents(array $consents): static
    {
        $this->consents = $consents;

        return $this;
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
