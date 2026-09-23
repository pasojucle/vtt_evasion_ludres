<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\Guest;
use App\Entity\Licence;
use App\Event\SessionPresenceToggledEvent;
use App\Repository\Interface\LicenceRepositoryInterface;
use App\Repository\Interface\SessionRepositoryInterface;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\MessageService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener()]
class TrialPeriodListener
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private LicenceService $licenceService,
        private MailerService $mailerService,
        private MessageService $messageService,
        private LicenceRepositoryInterface $licenceRepository,
    ) {
    }

    public function __invoke(SessionPresenceToggledEvent $event): void
    {
        $session = $event->session;

        $user = $session->getUser();

        if ($user instanceof Guest) {
            return;
        }

        $licence = $user->getLastLicence();
        if ($licence->getState()->isYearly()) {
            return;
        }

        $participations = $this->sessionRepository->findParticipationByUser($licence->getMember());
        if (Licence::MAX_TRIAL_SESSIONS <= $participations) {
            if ($this->licenceService->applyTransition($licence, 'complete_trial')) {
                $identity = $user->getIdentity();
                $this->mailerService->sendMailToMember(
                    $identity->getEmail(),
                    $identity->getFullName(),
                    'Fin de la période d\'essai',
                    $this->messageService->getMessageById('EMAIL_END_TESTING')
                );
            }
        } else {
            $this->licenceService->applyTransition($licence, 'uncomplete_trial');
        }
        $this->licenceRepository->save($licence);
    }
}
