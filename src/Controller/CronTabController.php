<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\Log\DeleteOutOfPeriod as LogDeleteOutOfPeriod;
use App\UseCase\Parameter\DisabledNewSeasonReRegistration;
use App\UseCase\Registration\FramerParticipation;
use App\UseCase\SecondHand\DisabledOutOfPeriod;
use App\UseCase\Slideshow\DeleteOutOfPeriod as SlideshowDeleteOutOfPeriod;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronTabController extends AbstractController
{
    #[Route('/crontab', name: 'crontab', methods: ['GET'])]
    public function disableOutOfPeriod(
        DisabledOutOfPeriod $disabledOutOfPeriod,
        DisabledNewSeasonReRegistration $disabledNewSeasonReRegistration,
        SlideshowDeleteOutOfPeriod $slideshowDeleteOutOfPeriod,
        LogDeleteOutOfPeriod $logDeleteOutOfPeriod,
        FramerParticipation $framerParticipation,
    ): Response {
        $results = [];

        try {
            $results[] = $disabledOutOfPeriod->execute();
            $results[] = $disabledNewSeasonReRegistration->execute();
            $results[] = $slideshowDeleteOutOfPeriod->execute();
            $results[] = $logDeleteOutOfPeriod->execute();
            $results[] = $framerParticipation->execute();
        } catch (Exception $exception) {
            return new JsonResponse(['codeError' => 1, 'error' => $exception->getMessage()]);
        }

        return new JsonResponse($results);
    }
}
