<?php

declare(strict_types=1);

namespace App\UseCase\v2\Licence;

use App\Dto\Service\MailerResult;
use App\Entity\Licence;
use App\Entity\Member;
use App\Repository\Interface\LicenceRepositoryInterface;
use App\Service\LicenceService;
use App\Service\MailerService;

class RejectLicence
{
    public function __construct(
        private LicenceService $licenceService,
        private LicenceRepositoryInterface $licenceRepository,
        private MailerService $mailerService,
    ) {
    }

    public function __invoke(Licence $licence, string $content): bool
    {
        $member = $licence->getMember();

        $result = $this->sendMail($content, $member);

        $tansition = ($licence->getState()->isYearly()) ? 'reject_yearly_file' : 'reject_trial_file';

        if ($result->success && $this->licenceService->applyTransition($licence, $tansition)) {
            $this->licenceRepository->save($licence);
            
            return true;
        }

        return false;
    }

    private function sendMail(string $content, Member $member): MailerResult
    {
        $mainIdentity = $member->getMainIdentity();

        return $this->mailerService->sendMailToMember(
            $mainIdentity->getEmail(),
            $mainIdentity->getFullName(),
            'Votre inscription au club de Vtt Évasion Ludres',
            $content,
        );
    }
}
