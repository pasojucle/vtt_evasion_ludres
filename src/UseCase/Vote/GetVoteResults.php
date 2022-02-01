<?php

declare(strict_types=1);

namespace App\UseCase\Vote;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use App\Repository\VoteResponseRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetVoteResults
{
    public function __construct(
        private TranslatorInterface $translator,
        private VoteResponseRepository $voteResponseRepository
    ) {
    }

    public function execute(Vote $vote): array
    {
        $voteResponsesByIssues = $this->voteResponseRepository->findResponsesByIssues($vote);
        $results = [];
        $values = [];
        foreach (array_keys(VoteResponse::VALUES) as $choice) {
            $values[$choice] = 0;
        }
        foreach ($voteResponsesByIssues as $responses) {
            foreach ($responses as $response) {
                if (VoteIssue::RESPONSE_TYPE_CHOICE === $response->getVoteIssue()->getResponseType()) {
                    $voteIssueId = $response->getVoteIssue()->getId();
                    if (!array_key_exists($voteIssueId, $results)) {
                        $results[$voteIssueId]['results'] = $values;
                        $results[$voteIssueId]['content'] = $response->getVoteIssue()->getContent();
                    }
                    ++$results[$voteIssueId]['results'][$response->getValue()];
                }
            }
        }

        return $results;
    }
}
