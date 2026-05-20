<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Entity\Licence;
use Doctrine\ORM\EntityManagerInterface;

class LicenceDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Licence $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}