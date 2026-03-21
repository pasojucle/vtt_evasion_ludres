<?php

declare(strict_types=1);

namespace App\UseCase\Licence;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Entity\Member;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\MessageService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ValidateLicence
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UserDtoTransformer $userDtoTransformer,
        private MessageService $messageService,
        private LicenceService $licenceService,
    ) {
    }

    public function execute(Request $request, Licence $licence)
    {
        $member = $licence->getMember();
        $licenceNumber = $member->getLicenceNumber();
        $this->licenceService->applyTransition($licence, 'register_to_federation');
        $this->entityManager->persist($licence);
        $data = $request->request->all('licence_validate');
        $this->setLicenceNumber($data, $member);
        $this->sendMail($licenceNumber, $member);
        $this->setMedicalCertificateDate($data, $member);

        $this->entityManager->flush();
    }

    private function setLicenceNumber(array $data, Member $member)
    {
        if (array_key_exists('licenceNumber', $data)) {
            $licenceNumber = $data['licenceNumber'];
            if (!empty($licenceNumber)) {
                $member->setLicenceNumber($licenceNumber);
                $this->entityManager->persist($member);
            }
        }
    }

    private function setMedicalCertificateDate(array $data, Member $member)
    {
        if (array_key_exists('medicalCertificateDate', $data)) {
            $medicalCertificateDate = $data['medicalCertificateDate'];
            if (!empty($medicalCertificateDate)) {
                $health = $member->getHealth();
                $medicalCertificateDate = DateTime::createFromFormat('d/m/Y', $medicalCertificateDate);
                $health->setMedicalCertificateDate($medicalCertificateDate);
                $this->entityManager->persist($health);
            }
        }
    }

    private function sendMail(string $licenceNumber, Member $member)
    {
        $userDto = $this->userDtoTransformer->fromEntity($member);
        if ($licenceNumber !== $userDto->licenceNumber) {
            $userDto = $this->userDtoTransformer->identifiersFromEntity($member);
            $subject = 'Votre numero de licence';
            $this->mailerService->sendMailToMember($userDto, $subject, $this->messageService->getMessageByName('EMAIL_LICENCE_VALIDATE'));
        }
    }
}
