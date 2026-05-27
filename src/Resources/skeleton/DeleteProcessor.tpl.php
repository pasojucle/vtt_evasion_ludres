<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Processor;

use App\Entity\<?= $entity_name ?>;
use Doctrine\ORM\EntityManagerInterface;

class <?= $entity_name ?>DeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(<?= $entity_name ?> $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}