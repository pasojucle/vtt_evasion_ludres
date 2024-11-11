<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideTypeDto;
use App\Entity\BikeRideType;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Message;

class BikeRideTypeDtoTransformer
{
    public function fromEntity(BikeRideType $bikeRideType): BikeRideTypeDto
    {
        $bikeRideTypeDto = new BikeRideTypeDto();
        $bikeRideTypeDto->entity = $bikeRideType;
        $bikeRideTypeDto->content = $bikeRideType->getContent();
        $bikeRideTypeDto->useLevels = $bikeRideType->isUseLevels();
        $bikeRideTypeDto->isShowMemberList = $bikeRideType->isShowMemberList();
        $bikeRideTypeDto->isSchool = RegistrationEnum::SCHOOL === $bikeRideType->getRegistration();
        $bikeRideTypeDto->isRegistrable = RegistrationEnum::NONE !== $bikeRideType->getRegistration();
        $bikeRideTypeDto->isNeedFramers = $bikeRideType->isNeedFramers();
        $bikeRideTypeDto->messages = $this->getMessages($bikeRideType);
        $bikeRideTypeDto->displayPractice = $bikeRideType->isDisplayBikeKind();

        return $bikeRideTypeDto;
    }

    private function getMessages(BikeRideType $bikeRideType): string|array
    {
        $messages = $bikeRideType->getMessages();

        if ($messages->count() < 1) {
            return 'Votre participation a bien éré prise en compte';
        }

        if (1 === $messages->count()) {
            return $messages->first()->getContent();
        }

        $messagesByLevelType = [];
        /** @var Message $message */
        foreach ($messages->toArray() as $message) {
            $levelType = $message->getLevelType() ?? 'default';
            $messagesByLevelType[$levelType] = $message->getContent();
        }
        return $messagesByLevelType;
    }
}
