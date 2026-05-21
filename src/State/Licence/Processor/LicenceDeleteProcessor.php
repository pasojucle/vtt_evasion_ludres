<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Entity\Licence;
use Doctrine\ORM\EntityManagerInterface;

class LicenceDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(Licence $entity): string
    {
        $fullName = $entity->getMember()->getIdentity()->getFullName();
        foreach ($entity->getLicenceAgreements() as $licenceAgreement) {
            $this->entityManager->remove($licenceAgreement);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return sprintf(
            'La licence de l\'utilisateur %s a bien été supprimée',
            $fullName
        );
    }
}
