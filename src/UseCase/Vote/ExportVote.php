<?php

declare(strict_types=1);

namespace App\UseCase\Vote;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use App\Repository\VoteResponseRepository;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportVote
{
    public function __construct(
        private TranslatorInterface $translator,
        private VoteResponseRepository $voteResponseRepository,
    ) {
    }

    public function execute(Vote $vote): string
    {
        $voteResponsesByUuid = $this->voteResponseRepository->findResponsesByUuid($vote);
        $content = [];
        $today = new DateTime();
        $content[] = 'Export du ' . $today->format('d/m/Y H:i:s') . ' - ' . $vote->getTitle();
        $content[] = '';
        $header = [];
        $header[0] = 'Identifiant';

        if (!$vote->getVoteIssues()->isEmpty()) {
            foreach ($vote->getVoteIssues() as $issue) {
                $header[] = $this->addQuote($issue->getContent());
            }
        }
        $content[] = implode(',', $header);

        if (!empty($voteResponsesByUuid)) {
            $this->addResponses($content, $voteResponsesByUuid);
            $results = $this->getResults($voteResponsesByUuid);
            $this->addRecap($content, $results, $vote);
            $this->addVoteUsers($content, $vote);
        }

        return implode(PHP_EOL, $content);
    }

    private function addQuote(?string $string): string
    {
        if (!$string) {
            $string = '';
        }

        return '"' . $string . '"';
    }

    private function addResponses(array &$content, array $voteResponsesByUuid): void
    {
        foreach ($voteResponsesByUuid as $uuid => $data) {
            $row = [];
            $row[] = $uuid;
            foreach ($data['responses'] as $response) {
                if (VoteIssue::RESPONSE_TYPE_CHOICE === $response->getVoteIssue()->getResponseType()) {
                    $value = $this->translator->trans(VoteResponse::VALUES[$response->getValue()]);
                } else {
                    $value = $response->getValue();
                }
                $row[] = $this->addQuote($value);
            }
            $content[] = implode(',', $row);
        }
    }

    private function GetResults(array $voteResponsesByUuid): array
    {
        $results = [];
        foreach (array_keys(VoteResponse::VALUES) as $choice) {
            $results[$choice] = [];
        }

        foreach ($voteResponsesByUuid as $data) {
            foreach ($data['responses'] as $response) {
                if (VoteIssue::RESPONSE_TYPE_CHOICE === $response->getVoteIssue()->getResponseType()) {
                    $voteIssueId = $response->getVoteIssue()->getId();

                    if (!array_key_exists($voteIssueId, $results[$response->getValue()])) {
                        foreach (array_keys(VoteResponse::VALUES) as $choice) {
                            $results[$choice][$voteIssueId] = 0;
                        }
                    }

                    ++$results[$response->getValue()][$voteIssueId];
                }
            }
        }

        return $results;
    }

    private function addRecap(array &$content, array $results, Vote $vote): void
    {
        $content[] = '';
        $header[0] = 'Récapitulatif';
        $content[] = implode(',', $header);
        if ($results) {
            ksort($results);
            foreach ($results as $choice => $resultsByChoice) {
                $row = [];
                $row[] = $this->translator->trans(VoteResponse::VALUES[$choice]);
                if (!$vote->getVoteIssues()->isEmpty()) {
                    foreach ($vote->getVoteIssues() as $issue) {
                        if (array_key_exists($issue->getId(), $resultsByChoice)) {
                            $row[] = $resultsByChoice[$issue->getId()];
                        }
                    }
                }
                $content[] = implode(',', $row);
            }
        }
    }

    private function addVoteUsers(array &$content, Vote $vote): void
    {
        $content[] = '';
        if (!$vote->getVoteUsers()->isEmpty()) {
            $content[] = 'Horodateur,Participants - ' . $vote->getVoteUsers()->count();
            foreach ($vote->getVoteUsers() as $voteUser) {
                $row = [];
                $identity = $voteUser->getUser()->getFirstIdentity();
                $row[] = $voteUser->getCreatedAt()->format('d/m/Y H:i');
                $row[] = $identity->getName() . ' ' . $identity->getFirstName();
                $content[] = implode(',', $row);
            }
        } else {
            $content[] = 'Aucun participant';
        }
    }
}
