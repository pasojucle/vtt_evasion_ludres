<?php

namespace App\Entity;

use App\Repository\HealthRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HealthRepository::class)
 */
class Health
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $socialSecurityNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */

     private $mutualCompany;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $mutualNumber;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $bloodGroup;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $tetanusBooster;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $doctorName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doctorAddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $doctorTown;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $doctorPhone;

    /**
     * @ORM\OneToMany(targetEntity=HealthQuestion::class, mappedBy="health")
     */
    private $healthQuestions;

    public function __construct()
    {
        $this->healthQuestions = new ArrayCollection();
        foreach (range(0, 8) as $number) {
            $healthQuestion = new HealthQuestion();
            $healthQuestion->setField($number);
            $this->addHealthQuestion($healthQuestion);
        }
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
}
