<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VoteIssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: VoteIssueRepository::class)]
class VoteIssue
{
    public const RESPONSE_TYPE_STRING = 1;

    public const RESPONSE_TYPE_CHOICE = 2;

    public const RESPONSE_TYPES = [
        self::RESPONSE_TYPE_STRING => 'vote.issue.string',
        self::RESPONSE_TYPE_CHOICE => 'vote.issue.choice',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: Vote::class, inversedBy: 'voteIssues')]
    #[JoinColumn(name: 'vote_id', referencedColumnName: 'id', nullable: false)]
    private Vote $vote;

    #[Column(type: 'string', length: 255)]
    private string $content;

    #[OneToMany(targetEntity: VoteResponse::class, mappedBy: 'voteIssue', cascade: ['persist', 'remove'])]
    private Collection $voteResponses;

    #[Column(type: 'integer')]
    private int $responseType;

    public function __construct()
    {
        $this->voteResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVote(): ?Vote
    {
        return $this->vote;
    }

    public function setVote(?Vote $vote): self
    {
        $this->vote = $vote;

        return $this;
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

    /**
     * @return Collection|VoteResponse[]
     */
    public function getVoteResponses(): Collection
    {
        return $this->voteResponses;
    }

    public function addVoteResponse(VoteResponse $voteResponse): self
    {
        if (!$this->voteResponses->contains($voteResponse)) {
            $this->voteResponses[] = $voteResponse;
            $voteResponse->setVoteIssue($this);
        }

        return $this;
    }

    public function removeVoteResponse(VoteResponse $voteResponse): self
    {
        if ($this->voteResponses->removeElement($voteResponse)) {
            // set the owning side to null (unless already changed)
            if ($voteResponse->getVoteIssue() === $this) {
                $voteResponse->setVoteIssue(null);
            }
        }

        return $this;
    }

    public function getResponseType(): ?int
    {
        return $this->responseType;
    }

    public function setResponseType(int $responseType): self
    {
        $this->responseType = $responseType;

        return $this;
    }
}