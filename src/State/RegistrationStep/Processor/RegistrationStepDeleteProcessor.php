<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Processor;

use App\Entity\RegistrationStep;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationStepDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(RegistrationStep $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}