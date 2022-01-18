<?php

namespace App\UseCase\Vote;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportVote
{
    public function __construct(private TranslatorInterface $translator)
    {
        
    }

    public function execute(Vote $vote, array $voteResponsesByUuid): string
    {
        $content = [];
        $header = [];
        if (!$vote->getVoteIssues()->isEmpty()) {
            foreach ($vote->getVoteIssues() as $issue) {
                $header[] = $this->addQuote($issue->getContent());
            }
        }
        $content[] = implode(',', $header);
        
        if (!empty($voteResponsesByUuid)) {
            foreach ($voteResponsesByUuid as $uuid) {
                $row = [];
                foreach ($uuid['responses'] as $response) {
                    $value = (VoteIssue::RESPONSE_TYPE_CHOICE === $response->getVoteIssue()->getResponseType()) 
                        ? $this->translator->trans(VoteResponse::VALUES[$response->getValue()])
                        : $response->getValue();
                    $row[] = $this->addQuote($value);
                }
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }

    private function addQuote(?string $string): string
    {
        if (!$string) {
            $string = '';
        }
        return '"'.$string.'"';
    }
}