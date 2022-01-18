<?php

namespace App\UseCase\Vote;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use App\Service\ParameterService;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetVote
{
    public function __construct(private ParameterService $parameterService)
    {
        
    }
    public function execute(Vote &$vote)
    {
        if (!$vote) {
            $vote = new Vote();
            $voteIssues = $this->parameterService->getParameterByName('VOTE_ISSUES');
            $vote->setContent($this->parameterService->getParameterByName('VOTE_CONTENT'));
            if (!empty($voteIssues)) {
                foreach ($voteIssues as $voteIssue) {
                    $issue = new VoteIssue();
                    $issue->setContent($voteIssue);
                    $vote->addVoteIssue($issue);
                }
            }
        }
    }
}