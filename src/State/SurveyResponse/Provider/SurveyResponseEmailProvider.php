<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;

use App\Entity\Survey;
use App\Mapper\EmailClipboardMapper;
use App\Repository\MemberRepository;

class SurveyResponseEmailProvider
{
    public function __construct(
        private MemberRepository $memberRepository,
        private EmailClipboardMapper $emailClipboardMapper,
    ) {
    }

    public function copyEmailListToClipboard(Survey $data): string
    {
        $members = $this->memberRepository->findRespondantsBySurvey($data);
        
        return $this->emailClipboardMapper->mapToEmailCsvString($members);
    }
}
