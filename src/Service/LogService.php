<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use App\Repository\LogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class LogService
{
    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function write(string $className, int $entityId, User $user): void
    {
        $log = $this->logRepository->findOneByEntityAndUser($className, $entityId, $user);
        if (!$log) {
            $log = new Log();
            $log->setEntity($className)
                ->setEntityId($entityId)
                ->setUser($user);
            $this->entityManager->persist($log);
        }

        $log->setViewAt(new DateTimeImmutable());
        $this->entityManager->flush();
    }
}
