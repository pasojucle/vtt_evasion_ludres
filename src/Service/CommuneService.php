<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Commune;
use App\Repository\CommuneRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class CommuneService
{
    public function __construct(private CommuneRepository $communeRepository, private EntityManagerInterface $entityManager)
    {
    }

    public function addIfNotExists(Commune $commune): Commune
    {
        if (!$commune->exists()) {
            $id = 'E' . $this->communeRepository->findCount();
            $commune->setId($id);
            $this->entityManager->persist($commune);
        }

        return $commune;
    }
}
