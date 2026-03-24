<?php

namespace App\Entity;

use App\Repository\GuestRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuestRepository::class)]
class Guest extends User
{
    #[ORM\Column(length: 255, nullable:true)]
    private ?string $email = null;

    #[ORM\Column(length: 64)]
    private ?string $token = null;

    #[ORM\Column]
    private ?DateTimeImmutable $tokenExpiresAt = null;

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenExpiresAt(): ?DateTimeImmutable
    {
        return $this->tokenExpiresAt;
    }

    public function setTokenExpiresAt(DateTimeImmutable $tokenExpiresAt): static
    {
        $this->tokenExpiresAt = $tokenExpiresAt;

        return $this;
    }

    public function getMainIdentity(): ?Identity
    {
        return $this->identity;
    }

    public function getContactEmail(): ?string
    {
        return $this->email;
    }
}
