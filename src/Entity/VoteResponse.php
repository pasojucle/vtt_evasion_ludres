<?php

namespace App\Entity;

use App\Repository\VoteResponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VoteResponseRepository::class)
 */
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

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=VoteIssue::class, inversedBy="voteResponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voteIssue;

    /**
     * @ORM\Column(type="text", nullable=true )
     */
    private ?string $value;

    /**
     * @ORM\Column(type="string", length=23)
     */
    private $uuid;

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
}
