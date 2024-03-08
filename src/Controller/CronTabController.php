<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\Parameter\DisabledNewSeasonReRegistration;
use App\UseCase\SecondHand\DisabledOutOfPeriod;
use App\UseCase\Slideshow\DeleteOutOfPeriod;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronTabController extends AbstractController
{
    #[Route('/occasion/disable/out/of/period', name: 'disable_out_of_period', methods: ['GET'])]
    #[Route('/crontab', name: 'crontab', methods: ['GET'])]
    public function disableOutOfPeriod(
        DisabledOutOfPeriod $disabledOutOfPeriod,
        DisabledNewSeasonReRegistration $disabledNewSeasonReRegistration,
        DeleteOutOfPeriod $deleteOutOfPeriod,
    ): Response {
        $results = [];

        try {
            $results[] = $disabledOutOfPeriod->execute();
            $results[] = $disabledNewSeasonReRegistration->execute();
            $results[] = $deleteOutOfPeriod->execute();
        } catch (Exception $exception) {
            return new JsonResponse(['codeError' => 1, 'error' => $exception->getMessage()]);
        }

        return new JsonResponse($results);
    }
}
