<?php

declare(strict_types=1);

namespace App\UseCase\Log;

use App\Repository\LogRepository;
use App\Service\ParameterService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class DeleteOutOfPeriod
{
    public function __construct(
        private ParameterService $parameterService,
        private LogRepository $logRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $duration = $this->parameterService->getParameterByName('LOG_DURATION');
        $deadline = (new DateTimeImmutable())->setTime(0, 0, 0)->sub(new DateInterval(sprintf('P%sD', $duration)));
        $logs = $this->logRepository->findOutOfPeriod($deadline);

        foreach ($logs as $log) {
            $this->entityManager->remove($log);
        }
        $this->entityManager->flush();

        return [
            'codeError' => 0,
             'message' => (empty($logs))
                ? 'no logs to disabling'
                : sprintf('%d logs disabled', count($logs)),
        ];
    }
}
