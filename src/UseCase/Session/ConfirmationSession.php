<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Session;
use App\Service\MailerService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfirmationSession
{
    public function __construct(
        private MailerService $mailerService,
        private UserDtoTransformer $userDtoTransformer,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private TranslatorInterface $translator,
    ) {
    }

    public function execute(Session $session): void
    {
        $user = $this->userDtoTransformer->fromEntity($session->getUser());
        $bikeRide = $this->bikeRideDtoTransformer->fromEntity($session->getCluster()->getBikeRide());

        $messages = $bikeRide->bikeRideType->messages;
        $content = (is_string($messages)) ? $messages : $this->getMessageByLevelType($messages, $user->level->type);

        // $parameterName = (Licence::CATEGORY_MINOR === $user->lastLicence->category)
        //     ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR'
        //     : (Level::TYPE_FRAME === $user->level->type && $bikeRide->bikeRideType->isSchool ? 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER' : 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT');
        
        $subject = sprintf('Confirmation d\'inscription à %s du %s', $bikeRide->title, $bikeRide->period);
        // $content = $this->parameterService->getParameterByName($parameterName);
        $additionalParams = [
                '{{ bikeRideTitleAndPeriod }}' => sprintf('%s du %s', $bikeRide->title, $bikeRide->period),
                '{{ disponibilite }}' => $this->availabilityToString($session->getAvailability()),
            ];

        $this->mailerService->sendMailToMember($user, $subject, $content, null, $additionalParams);
    }

    private function availabilityToString(?AvailabilityEnum $availability): ?string
    {
        if ($availability) {
            $availabilities = [
                AvailabilityEnum::REGISTERED->name => 'session.availability_status.presence',
                AvailabilityEnum::AVAILABLE->name => 'session.availability_status.availability',
                AvailabilityEnum::UNAVAILABLE->name => 'session.availability_status.absence'
            ];
            dump($availabilities, $availability);
            
            return $this->translator->trans($availabilities[$availability->name]);
        }

        return null;
    }

    private function getMessageByLevelType(array $messages, int $levelType): string
    {
        if (array_key_exists($levelType, $messages)) {
            return $messages[$levelType];
        }

        return $messages['default'];
    }
}
