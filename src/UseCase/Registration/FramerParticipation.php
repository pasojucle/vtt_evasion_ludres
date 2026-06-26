<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FramerParticipation
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly MessageService $messageService,
        private readonly MailerService $mailerService,
        private readonly BikeRideDtoTransformer $bikeRideDtoTransformer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function execute(): array
    {
        $sessions = $this->sessionRepository->findFramerAvailability();
        if (!empty($sessions)) {
            $message = $this->messageService->getMessageById('CONFIRM_FRAMER_PARTICIPATION_EMAIL');
            $subject = sprintf('Confirmation à votre participation à la sortie de l\'école VTT');

            /** @var Session $session */
            foreach ($sessions as $session) {
                $mainIdentity = $session->getMember()->getMainIdentity();
                $bikeRideDto = $this->bikeRideDtoTransformer->getHeaderFromEntity($session->getCluster()->getBikeRide());
                $params = [
                    '{{ rando }}' => sprintf('%s du %s', $bikeRideDto->title, $bikeRideDto->period),
                    '{{ lien_modifier_disponibilite }}' => $this->urlGenerator->generate('session_availability_edit', ['session' => $session->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
                $this->mailerService->sendMailToMember($$mainIdentity->getEmail(), $mainIdentity->getFullName(), $subject, $message);
            }
        }

        return [
            'codeError' => 0,
             'message' => (empty($sessions))
                ? 'no send message to confirm framer availabilities'
                : sprintf('%d messages send to confirm framer availabilities', count($sessions)),
        ];
    }
}
