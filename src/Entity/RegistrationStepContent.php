<?php

namespace App\Entity;

use App\Repository\RegistrationStepContentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegistrationStepContentRepository::class)
 * @ORM\EntityListeners({"App\EventListeners\EntityListener"})
 */
class RegistrationStepContent
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $formChildren;

    /**
     * @ORM\ManyToOne(targetEntity=RegistrationStep::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $registrationStep;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getFormChildren(): ?int
    {
        return $this->formChildren;
    }

    public function setFormChildren(?int $formChildren): self
    {
        $this->formChildren = $formChildren;

        return $this;
    }

    public function getRegistrationStep(): ?RegistrationStep
    {
        return $this->registrationStep;
    }

    public function setRegistrationStep(?RegistrationStep $registrationStep): self
    {
        $this->registrationStep = $registrationStep;

        return $this;
    }
}
