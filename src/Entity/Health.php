<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use App\Repository\HealthRepository;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity(repositoryClass: HealthRepository::class)]
class Health
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $medicalCertificateDate = null;

    #[Column(type:'boolean', options: ['default' => false])]
    private bool $atLeastOnePositveResponse = false;

    #[OneToOne(targetEntity: User::class, mappedBy: 'health')]
    private ?User $user;

    private Collection $healthQuestions;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    public function __construct()
    {
        $this->healthQuestions = new ArrayCollection();
    }

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

    public function hasAtLeastOnePositveResponse(): ?bool
    {
        return $this->atLeastOnePositveResponse;
    }

    public function setAtLeastOnePositveResponse(): self
    {
        $atLeastOnePositveResponse = false;

        foreach ($this->healthQuestions as $question) {
            if (true === $question->getValue()) {
                $atLeastOnePositveResponse = true;
            }
        }

        $this->atLeastOnePositveResponse = $atLeastOnePositveResponse;
        return $this;
    }

    public function getHealthQuestions(): Collection
    {
        return $this->healthQuestions;
    }

    public function setHealthQuestions(ArrayCollection $healthQuestions): self
    {
        $this->healthQuestions = $healthQuestions;

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
