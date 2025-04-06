<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use App\Form\LogType;
use App\Repository\LogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LogService
{
    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
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

    public function getForm(?array $data = null): FormInterface
    {
        return $this->formFactory->create(LogType::class, $data, [
            'action' => $this->urlGenerator->generate('log_write'),
        ]);
    }
}
