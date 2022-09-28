<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HealthRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: HealthRepository::class)]
class Health
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 15, nullable: true)]
    private ?string $socialSecurityNumber = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $mutualCompany = null;

    #[Column(type: 'string', length: 25, nullable: true)]
    private ?string $mutualNumber = null;

    #[Column(type: 'string', length: 5, nullable: true)]
    private ?string $bloodGroup = null;

    #[Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $tetanusBooster = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $doctorName = null;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $doctorAddress = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $doctorTown = null;

    #[Column(type: 'string', length: 10, nullable: true)]
    private ?string $doctorPhone = null;

    #[OneToMany(targetEntity: HealthQuestion::class, mappedBy: 'health')]
    private Collection $healthQuestions;

    #[OneToMany(targetEntity: Disease::class, mappedBy: 'health')]
    private Collection $diseases;

    #[Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $medicalCertificateDate = null;

    public function __construct()
    {
        $this->healthQuestions = new ArrayCollection();
        $this->diseases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSocialSecurityNumber(): ?string
    {
        return $this->socialSecurityNumber;
    }

    public function setSocialSecurityNumber(string $socialSecurityNumber): self
    {
        $this->socialSecurityNumber = $socialSecurityNumber;

        return $this;
    }

    public function getMutualCompany(): ?string
    {
        return $this->mutualCompany;
    }

    public function setMutualCompany(?string $mutualCompany): self
    {
        $this->mutualCompany = $mutualCompany;

        return $this;
    }

    public function getMutualNumber(): ?string
    {
        return $this->mutualNumber;
    }

    public function setMutualNumber(?string $mutualNumber): self
    {
        $this->mutualNumber = $mutualNumber;

        return $this;
    }

    public function getBloodGroup(): ?string
    {
        return $this->bloodGroup;
    }

    public function setBloodGroup(?string $bloodGroup): self
    {
        $this->bloodGroup = $bloodGroup;

        return $this;
    }

    public function getTetanusBooster(): ?\DateTimeInterface
    {
        return $this->tetanusBooster;
    }

    public function setTetanusBooster(?\DateTimeInterface $tetanusBooster): self
    {
        $this->tetanusBooster = $tetanusBooster;

        return $this;
    }

    public function getDoctorName(): ?string
    {
        return $this->doctorName;
    }

    public function setDoctorName(?string $doctorName): self
    {
        $this->doctorName = $doctorName;

        return $this;
    }

    public function getDoctorAddress(): ?string
    {
        return $this->doctorAddress;
    }

    public function setDoctorAddress(?string $doctorAddress): self
    {
        $this->doctorAddress = $doctorAddress;

        return $this;
    }

    public function getDoctorTown(): ?string
    {
        return $this->doctorTown;
    }

    public function setDoctorTown(?string $doctorTown): self
    {
        $this->doctorTown = $doctorTown;

        return $this;
    }

    public function getDoctorPhone(): ?string
    {
        return $this->doctorPhone;
    }

    public function setDoctorPhone(?string $doctorPhone): self
    {
        $this->doctorPhone = $doctorPhone;

        return $this;
    }

    /**
     * @return Collection|HealthQuestion[]
     */
    public function getHealthQuestions(): Collection
    {
        return $this->healthQuestions;
    }

    public function addHealthQuestion(HealthQuestion $healthQuestion): self
    {
        if (!$this->healthQuestions->contains($healthQuestion)) {
            $this->healthQuestions[] = $healthQuestion;
            $healthQuestion->setHealth($this);
        }

        return $this;
    }

    public function removeHealthQuestion(HealthQuestion $healthQuestion): self
    {
        if ($this->healthQuestions->removeElement($healthQuestion)) {
            // set the owning side to null (unless already changed)
            if ($healthQuestion->getHealth() === $this) {
                $healthQuestion->setHealth(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Disease[]
     */
    public function getDiseases(): Collection
    {
        return $this->diseases;
    }

    public function addDisease(Disease $disease): self
    {
        if (!$this->diseases->contains($disease)) {
            $this->diseases[] = $disease;
            $disease->setHealth($this);
        }

        return $this;
    }

    public function removeDisease(Disease $disease): self
    {
        if ($this->diseases->removeElement($disease)) {
            // set the owning side to null (unless already changed)
            if ($disease->getHealth() === $this) {
                $disease->setHealth(null);
            }
        }

        return $this;
    }

    public function getMedicalCertificateDate(): ?\DateTimeInterface
    {
        return $this->medicalCertificateDate;
    }

    public function setMedicalCertificateDate(?\DateTimeInterface $medicalCertificateDate): self
    {
        $this->medicalCertificateDate = $medicalCertificateDate;

        return $this;
    }
}
