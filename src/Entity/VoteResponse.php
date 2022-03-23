<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VoteResponseRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: VoteResponseRepository::class)]
class VoteResponse
{
    public const VALUE_NO = 0;

    public const VALUE_YES = 1;

    public const VALUE_NO_OPINION = 2;

    public const VALUES = [
        self::VALUE_NO => 'vote.response.no',
        self::VALUE_YES => 'vote.response.yes',
        self::VALUE_NO_OPINION => 'vote.response.no_opinion',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: VoteIssue::class, inversedBy: 'voteResponses')]
    #[JoinColumn(nullable: false)]
    private $voteIssue;

    #[Column(type: 'text', nullable: true)]
    private ?string $value;

    #[Column(type: 'string', length: 23)]
    private string $uuid;

    #[ManyToOne(targetEntity: User::class)]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVoteIssue(): ?VoteIssue
    {
        return $this->voteIssue;
    }

    public function setVoteIssue(?VoteIssue $voteIssue): self
    {
        $this->voteIssue = $voteIssue;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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
}
