<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use DateTime;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Session;
use App\Service\MailerService;
use App\ViewModel\UserPresenter;
use App\ViewModel\BikeRidePresenter;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfirmationSession
{
    public function __construct(
        private MailerService $mailerService,
        private UserPresenter $userPresenter,
        private BikeRidePresenter $bikeRidePresenter,
        private TranslatorInterface $translator
    ) {
    }

    public function execute(Session $session): void
    {
        $this->userPresenter->present($session->getUser());
        $this->bikeRidePresenter->present($session->getCluster()->getBikeRide());
        $user = $this->userPresenter->viewModel();
        $bikeRide = $this->bikeRidePresenter->viewModel();
        $content = (Licence::CATEGORY_MINOR === $user->seasonLicence->category)
            ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR'
            : (Level::TYPE_FRAME === $user->level->type ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER' : 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT');
        
        $this->mailerService->sendMailToMember([
            'name' => $user->member->name,
            'firstName' => $user->member->firstName,
            'email' => $user->mainEmail,
            'subject' => 'Confirmation d\'inscription à une sortie',
            'bikeRideTitleAndPeriod' => $bikeRide->title . ' du ' . $bikeRide->period,
            'sessionAvailability' => $this->availabilityToString($session->getAvailability()),
        ], $content);
    }

    private function availabilityToString(?int $availability): ?string
    {
        if ($availability) {
            $availabilities = [
                Session::AVAILABILITY_REGISTERED => 'session.availability_status.presence',
                Session::AVAILABILITY_AVAILABLE => 'session.availability_status.availability',
                Session::AVAILABILITY_UNAVAILABLE => 'session.availability_status.absence'
            ];
            
            return $this->translator->trans($availabilities[$availability]);
        }

        return null;
    }
}
