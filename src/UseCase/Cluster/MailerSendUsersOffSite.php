<?php

declare(strict_types=1);

namespace App\UseCase\Cluster;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Member;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ReplaceKeywordsService;
use Symfony\Bundle\SecurityBundle\Security;

class MailerSendUsersOffSite
{
    public function __construct(
        private MailerService $mailerService,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywords,
        private Security $security,
        private UserDtoTransformer $userDtoTransformer,
    ) {
    }

    public function execute(array $usersOffSite, BikeRide $bikeRide): void
    {
        if (!empty($usersOffSite)) {
            $content = $this->messageService->getMessageByName('BIKE_RIDE_ABSENCE_EMAIL');
            $bikeRideTitle = $bikeRide->getTitle();
            foreach ($usersOffSite['users'] as $member) {
                $this->replaceKeywords->replaceFromParams($content, $this->additionalParams($bikeRideTitle));
                $this->mailerService->sendMailToMember($member->email, $member->fullName, $bikeRideTitle, $content);
            }
        }
    }

    public function additionalParams(string $bikeRideTitle): array
    {
        /** @var Member $framer */
        $framer = $this->security->getUser();
        $framerTdto = $this->userDtoTransformer->identifiersFromEntity($framer);
        return [
            '{{ nom_rando }}' => $bikeRideTitle,
            '{{ nom_encadrant }}' => $framerTdto->member->fullName,
            '{{ telephone_encadrant }}' => $framerTdto->member->phone,
        ];
    }
}
