<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\Member;
use App\Event\ClusterCompleteToggleEvent;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\Placeholder\DataPlaceholder;
use App\Service\Placeholder\PlaceholderResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener()]
class clusterAbsentParticipantsListener
{
    public function __construct(
        private MailerService $mailerService,
        private MessageService $messageService,
        private Security $security,
        private PlaceholderResolver $placeholderResolver,
    ) {
    }

    public function __invoke(ClusterCompleteToggleEvent $event): void
    {
        $bikeRide = $event->bikeRide;
        $absentParticipants = $event->absentParticipants;
        $bikeRideTitle = $bikeRide->getTitle();
        $content = $this->messageService->getMessageById('BIKE_RIDE_ABSENCE_EMAIL');

        /** @var Member $framer */
        $framer = $this->security->getUser();
        $framerIdentity = $framer->getIdentity();

        $placeholderParams = new DataPlaceholder([
            'nom_rando' => $bikeRideTitle,
            'nom_encadrant' => $framerIdentity->getFullName(),
            'telephone_encadrant' => $framerIdentity->getMobile(),
        ]);

        foreach ($absentParticipants as $member) {
            $fullName = $member->getIdentity()->getFullName();
            $email = $member->getMainIdentity()->getEmail();
            $message = $this->placeholderResolver->resolve($content, $member, $placeholderParams);
            $this->mailerService->sendMailToMember($email, $fullName, $bikeRideTitle, $message);
        }
    }
}
