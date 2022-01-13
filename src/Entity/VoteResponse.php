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
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="voteResponses")
     * @ORM\JoinColumn(nullable=false)
     */
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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

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
