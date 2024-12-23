<?php

declare(strict_types=1);

namespace App\UseCase\Licence;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Entity\User;
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
    ) {
    }

    public function execute(Request $request, Licence $licence)
    {
        $user = $licence->getUser();
        $licenceNumber = $user->getLicenceNumber();
        $status = ($licence->isFinal()) ? Licence::STATUS_VALID : Licence::STATUS_TESTING;
        $licence->setStatus($status);
        $this->entityManager->persist($licence);
        $data = $request->request->all('licence_validate');
        $this->setLicenceNumber($data, $user);
        $this->sendMail($licenceNumber, $user);
        $this->setMedicalCertificateDate($data, $user);

        $this->entityManager->flush();
    }

    private function setLicenceNumber(array $data, User $user)
    {
        if (array_key_exists('licenceNumber', $data)) {
            $licenceNumber = $data['licenceNumber'];
            if (!empty($licenceNumber)) {
                $user->setLicenceNumber($licenceNumber);
                $this->entityManager->persist($user);
            }
        }
    }

    private function setMedicalCertificateDate(array $data, User $user)
    {
        if (array_key_exists('medicalCertificateDate', $data)) {
            $medicalCertificateDate = $data['medicalCertificateDate'];
            if (!empty($medicalCertificateDate)) {
                $health = $user->getHealth();
                $medicalCertificateDate = DateTime::createFromFormat('d/m/Y', $medicalCertificateDate);
                $health->setMedicalCertificateDate($medicalCertificateDate);
                $this->entityManager->persist($health);
            }
        }
    }

    private function sendMail(string $licenceNumber, User $user)
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);
        if ($licenceNumber !== $userDto->licenceNumber) {
            $userDto = $this->userDtoTransformer->identifiersFromEntity($user);
            $subject = 'Votre numero de licence';
            $this->mailerService->sendMailToMember($userDto, $subject, $this->messageService->getMessageByName('EMAIL_LICENCE_VALIDATE'));
        }
    }
}
