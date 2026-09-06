<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\Enum\AvailabilityEnum;
use App\State\Interface\FormTurboStreamProcessorInterface;
use App\UseCase\v2\Session\CreateSession;
use Doctrine\ORM\EntityManagerInterface;

class SessionCreateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private CreateSession $createSession,
    ) {
    }

    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $bikeRide = $payload->cluster->getBikeRide();
        $user = $payload->user;
        ($this->createSession)(
            $bikeRide,
            $payload->cluster,
            $user,
            $payload->practice,
            $payload->bikeType,
            ($bikeRide->getBikeRideType()->isNeedFramers() && $user->isFramer())
                ? AvailabilityEnum::REGISTERED
                : AvailabilityEnum::NONE,
        );
                
        // $responses = array_key_exists('responses', $data) ? $data['responses'] : null;
        // if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
        //     $this->addSurveyResponses($surveyResponses, $member, $bikeRide);
        // }

    
        return new TurboStreamProcessorResult(true);
    }
}