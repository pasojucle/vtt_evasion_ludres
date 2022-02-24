<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use App\Repository\LogErrorRepository;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity(repositoryClass: LogErrorRepository::class)]
class LogError
{
    #[Column(type: "integer")]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: "string", length: 255)]
    private string $url;

    private ?string $message = null;

    #[Column(type: "string", length: 255)]
    private string $userAgent;

    #[Column(type: "string", length: 255, nullable: true)]
    private ?string $fileName;

    #[Column(type: "integer", nullable: true)]
    private ?int $line;

    #[Column(type: "integer", nullable: true)]
    private ?int $statusCode;

    #[Column(type: "string", length: 255, nullable: true)]
    private ?string $route;

    #[Column(type: "text")]
    private string $errorMessage;

    private $persist = true;

    #[ManyToOne(targetEntity: User::class)]
    private ?User $user;

    #[Column(type: "datetime")]
    private DateTime $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function setLine(?int $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getPersist(): bool
    {
        return $this->persist;
    }

    public function setPersist(bool $persist): self
    {
        $this->persist = $persist;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
