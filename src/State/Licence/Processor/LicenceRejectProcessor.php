<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\Payload\LicenceReject;
use App\Dto\Service\MailerResult;
use App\Dto\State\RedirectProcessorResult;
use App\Entity\Member;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements FormRedirectProcessorInterface<LicenceReject>
 */
class LicenceRejectProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
        private MailerService $mailerService,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $licence = $entity->licence;
        $member = $licence->getMember();

        $result = $this->sendMail($entity->content, $member);

        $tansition = ($licence->getState()->isYearly()) ? 'reject_yearly_file' : 'reject_trial_file';

        if ($result->success && $this->licenceService->applyTransition($licence, $tansition)) {
            $this->entityManager->persist($licence);
            $this->entityManager->flush();

            return new RedirectProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.reject',
                flashType: 'success',
            );
        }

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.error.reject',
            flashType: 'danger',
        );
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
