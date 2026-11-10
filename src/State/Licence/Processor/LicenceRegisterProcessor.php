<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Dto\Payload\LicenceRegister;
use App\Dto\Service\MailerResult;
use App\Entity\Member;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\UrlContextService;
use App\State\Message\Provider\MessageProvider;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class LicenceRegisterProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
        private MailerService $mailerService,
        private MessageProvider $messageProvider,
        private UrlContextService $urlContextService
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        /**
         * @var LicenceRegister $data
         */
        $data = $payload->data;
        $licence = $data->licence;
        
        $member = $licence->getMember();
        $licenceNumber = $member->getLicenceNumber();
        $this->licenceService->applyTransition($licence, 'register_to_federation');
        $this->entityManager->persist($licence);
        $this->setLicenceNumber($data, $member);
        $this->setMedicalCertificateDate($data, $member);

        $this->entityManager->flush();

        $result = $this->sendMail($licenceNumber, $member);
        if (false === $result?->success) {
            return new RedirectProcessorResult(
                success: false,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.danger.received',
                flashType: 'danger',
            );
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.success.received',
            flashType: 'success',
        );
    }

    private function setLicenceNumber(LicenceRegister $licenceRegister, Member $member): void
    {
        if ($licenceRegister->licenceNumber) {
            $member->setLicenceNumber($licenceRegister->licenceNumber);
        }
    }

    private function setMedicalCertificateDate(LicenceRegister $licenceRegister, Member $member): void
    {
        if ($licenceRegister->medicalCertificateDate) {
            $health = $member->getHealth();
            $health->setMedicalCertificateDate($licenceRegister->medicalCertificateDate);
        }
    }

    private function sendMail(string $licenceNumber, Member $member): ?MailerResult
    {
        if ($licenceNumber !== $member->getLicenceNumber()) {
            $mainIdentity = $member->getMainIdentity();
            return $this->mailerService->sendMailToMember(
                $mainIdentity->getEmail(),
                $mainIdentity->getFullName(),
                'Votre numero de licence',
                $this->messageProvider->getMessageById('EMAIL_LICENCE_VALIDATE', $member)
            );
        }

        return null;
    }
}
