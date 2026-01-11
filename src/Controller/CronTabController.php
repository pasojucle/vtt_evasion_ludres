<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\CronTab\CronTabLog;
use App\UseCase\Licence\ExpireLicence;
use App\UseCase\Log\DeleteOutOfPeriod as LogDeleteOutOfPeriod;
use App\UseCase\Parameter\DisabledNewSeasonReRegistration;
use App\UseCase\Registration\FramerParticipation;
use App\UseCase\SecondHand\DisabledOutOfPeriod;
use App\UseCase\Slideshow\DeleteOutOfPeriod as SlideshowDeleteOutOfPeriod;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CronTabController extends AbstractController
{
    #[Route('/crontab', name: 'crontab', methods: ['GET'])]
    public function disableOutOfPeriod(
        DisabledOutOfPeriod $disabledOutOfPeriod,
        DisabledNewSeasonReRegistration $disabledNewSeasonReRegistration,
        SlideshowDeleteOutOfPeriod $slideshowDeleteOutOfPeriod,
        LogDeleteOutOfPeriod $logDeleteOutOfPeriod,
        FramerParticipation $framerParticipation,
        ExpireLicence $expireLicence,
        CronTabLog $cronTabLog,
    ): Response {
        $results = [];

        try {
            $results[] = $disabledOutOfPeriod->execute();
            $results[] = $disabledNewSeasonReRegistration->execute();
            $results[] = $slideshowDeleteOutOfPeriod->execute();
            $results[] = $logDeleteOutOfPeriod->execute();
            $results[] = $framerParticipation->execute();
            $results[] = $expireLicence->execute();
        } catch (Exception $exception) {
            $cronTabLog->write(1, $results, $exception->getMessage());
            return new JsonResponse(['codeError' => 1, 'error' => $exception->getMessage()]);
        }

        $cronTabLog->write(0, $results);
        return new JsonResponse($results);
    }
}
