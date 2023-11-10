<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Session;
use App\Service\MailerService;
use App\Service\ParameterService;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfirmationSession
{
    public function __construct(
        private MailerService $mailerService,
        private UserDtoTransformer $userDtoTransformer,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private TranslatorInterface $translator,
        private ParameterService $parameterService,
    ) {
    }

    public function execute(Session $session): void
    {
        $user = $this->userDtoTransformer->fromEntity($session->getUser());
        $bikeRide = $this->bikeRideDtoTransformer->fromEntity($session->getCluster()->getBikeRide());

        $parameterName = (Licence::CATEGORY_MINOR === $user->lastLicence->category)
            ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR'
            : (Level::TYPE_FRAME === $user->level->type && $bikeRide->bikeRideType->isSchool ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER' : 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT');
        
        $subject = 'Confirmation d\'inscription à une sortie';
    
        $search = ['{{ bikeRideTitleAndPeriod }}', '{{ disponibilite }}'];
        $replace = [
                'bikeRideTitleAndPeriod' => $bikeRide->title . ' du ' . $bikeRide->period,
                'sessionAvailability' => $this->availabilityToString($session->getAvailability()),
            ];
            
        $this->mailerService->sendMailToMember($user, $subject, str_replace($search, $replace, $this->parameterService->getParameterByName($parameterName)));
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
