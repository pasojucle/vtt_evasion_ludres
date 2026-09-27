<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Enum\AvailabilityEnum;
use App\UseCase\v2\Session\CreateSession;

class SessionCreateProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private CreateSession $createSession,
    ) {
    }

    /**
     * Summary of process
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $data = $payload->data;
        $bikeRide = $data->cluster->getBikeRide();
        $user = $data->user;
        ($this->createSession)(
            $bikeRide,
            $data->cluster,
            $user,
            $data->practice,
            $data->bikeType,
            ($bikeRide->getBikeRideType()->isNeedFramers() && $user->isFramer())
                ? AvailabilityEnum::REGISTERED
                : AvailabilityEnum::NONE,
        );
                
        // $responses = array_key_exists('responses', $data) ? $data['responses'] : null;
        // if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
        //     $this->addSurveyResponses($surveyResponses, $member, $bikeRide);
        // }

    
        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'session.flash.success.create',
            data: $data
        );
    }
}
