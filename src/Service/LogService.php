<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use App\Form\LogType;
use App\Repository\LogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
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
        private readonly Security $security,
    ) {
    }

    public function write(string $className, int $entityId, ?User $user = null): void
    {
        if (!$user) {
            /** @var User $user */
            $user = $this->security->getUser();
        }
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

    public function writeFromEntity(object $entity, ?User $user = null): void
    {
        $className = (new ReflectionClass($entity))->getShortName();

        $this->write($className, $entity->getId(), $user);
    }

    public function getForm(?array $data = null): FormInterface
    {
        return $this->formFactory->create(LogType::class, $data, [
            'action' => $this->urlGenerator->generate('log_write'),
        ]);
    }
}
