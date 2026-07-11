<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\Form\LicenceReject;
use App\Dto\Service\MailerResult;
use App\Dto\State\HtmlProcessorResult;
use App\Entity\Member;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\UrlContextService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceRejectProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
        private MailerService $mailerService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var LicenceReject $entity */

        $licence = $entity->licence;
        $member = $licence->getMember();

        $result = $this->sendMail($entity->content, $member);

        $tansition = ($licence->getState()->isYearly()) ? 'reject_yearly_file' : 'reject_trial_file';

        if ($result->success && $this->licenceService->applyTransition($licence, $tansition)) {
            $this->entityManager->persist($licence);
            $this->entityManager->flush();

            return new HtmlProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.reject',
                flashType: 'success',
            );
        }

        return new HtmlProcessorResult(
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
