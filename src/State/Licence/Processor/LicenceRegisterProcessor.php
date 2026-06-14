<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\Form\LicenceRegister;
use App\Dto\State\ProcessorResult;
use App\Dto\Service\MailerResult;
use App\Entity\Member;
use App\Service\FilterDecoderService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\State\DialogProcessorInterface;
use App\State\Message\Provider\MessageProvider;
use Doctrine\ORM\EntityManagerInterface;

class LicenceRegisterProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
        private FilterDecoderService $filterDecoder,
        private MailerService $mailerService,
        private MessageProvider $messageProvider,
    ) {
    }

    public function process(object $licenceRegister, ?string $filter): ProcessorResult
    {
        assert($licenceRegister instanceof LicenceRegister);
        $licence = $licenceRegister->licence;
        
        $member = $licence->getMember();
        $licenceNumber = $member->getLicenceNumber();
        $this->licenceService->applyTransition($licence, 'register_to_federation');
        $this->entityManager->persist($licence);
        $this->setLicenceNumber($licenceRegister, $member);
        $this->setMedicalCertificateDate($licenceRegister, $member);

        $this->entityManager->flush();

        $result = $this->sendMail($licenceNumber, $member);
        if (false === $result?->success) {
            return new ProcessorResult(
            success: false,
            targetRoute: 'admin_registration_list',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'registration.flash.danger.received',
            flashType: 'danger',
        );
        }

        return new ProcessorResult(
            success: true,
            targetRoute: 'admin_registration_list',
            routeParams: $this->filterDecoder->decode($filter),
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
                $this->messageProvider->getMessageByName('EMAIL_LICENCE_VALIDATE', $member)
            );
        }

        return null;
    }
}
