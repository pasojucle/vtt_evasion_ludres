<?php

declare(strict_types=1);

namespace App\UseCase\Cluster;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\User;
use App\Service\MailerService;
use App\Service\ParameterService;
use Symfony\Bundle\SecurityBundle\Security;

class MailerSendUsersOffSite
{
    public function __construct(
        private MailerService $mailerService,
        private ParameterService $parameterService,
        private Security $security,
        private UserDtoTransformer $userDtoTransformer,
    ) {
    }

    public function execute(array $usersOffSite, BikeRide $bikeRide): void
    {
        if (!empty($usersOffSite)) {
            $content = $this->parameterService->getParameterByName('BIKE_RIDE_ABSENCE_EMAIL');
            $bikeRideTitle = $bikeRide->getTitle();
            foreach ($usersOffSite['users'] as $member) {
                $this->mailerService->sendMailToMember($member, $bikeRideTitle, $content, null, $this->additionalParams($bikeRideTitle));
            }
        }
    }

    public function additionalParams(string $bikeRideTitle): array
    {
        /** @var User $framer */
        $framer = $this->security->getUser();
        $framerTdto = $this->userDtoTransformer->identifiersFromEntity($framer);
        return [
            '{{ nom_rando }}' => $bikeRideTitle,
            '{{ nom_encadrant }}' => $framerTdto->member->fullName,
            '{{ telephone_encadrant }}' => $framerTdto->member->phone,
        ];
    }
}
