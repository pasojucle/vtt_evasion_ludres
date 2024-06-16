<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use App\Repository\LogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LogService
{
    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    private function write(Log $log): void
    {
        $log->setViewAt(new DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function writeByEntity(string $className, int $entityId, User $user): void
    {
        $log = $this->logRepository->findOneByEntityAndUser($className, $entityId, $user);
        if (!$log) {
            $log = new Log();
            $log->setEntity($className)
                ->setEntityId($entityId)
                ->setUser($user);
            $this->entityManager->persist($log);
        }

        $this->write($log);
    }

    public function WriteByRoute(string $route): void
    {
        /**  @var User $user */
        $user = $this->security->getUser();
        if ($user) {
            $log = $this->logRepository->findOneByRouteAndUser($route, $user);
            if (!$log) {
                $log = new Log();
                $log->setRoute($route)
                    ->setUser($user);
                $this->entityManager->persist($log);
            }

            $this->write($log);
        }
    }
}
