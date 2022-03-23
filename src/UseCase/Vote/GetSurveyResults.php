<?php

declare(strict_types=1);

namespace App\UseCase\Vote;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use App\Repository\VoteResponseRepository;
use Symfony\Component\Form\Form;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetSurveyResults
{
    public function __construct(
        private TranslatorInterface $translator,
        private VoteResponseRepository $voteResponseRepository
    ) {
    }

    public function execute(array $filter): array
    {

        return $this->voteResponseRepository->findByfilter($filter);
    }
}
