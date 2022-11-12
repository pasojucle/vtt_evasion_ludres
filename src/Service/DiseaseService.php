<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Disease;
use App\Entity\User;
use App\Repository\DiseaseKindRepository;
use Doctrine\ORM\EntityManagerInterface;

class DiseaseService
{
    public function __construct(private DiseaseKindRepository $diseaseKindRepository, private EntityManagerInterface $entityManager)
    {
    }
    public function updateAndSortdiseases(User &$user, int $licenceCategory): void
    {
        $diseaseKinds = $this->diseaseKindRepository->findAllOrderByCategory($licenceCategory);
        $diseases = $user->getHealth()->getDiseases()->getValues();

        $user->getHealth()->getDiseases()->clear();

        foreach ($diseaseKinds as $diseaseKind) {
            $currentDisease = null;
            foreach ($diseases as $disease) {
                if ($disease->getDiseaseKind()->getId() === $diseaseKind->getId()) {
                    $currentDisease = $disease;
                }
            }
                
            if (null === $currentDisease) {
                $currentDisease = new Disease();
                $currentDisease->setDiseaseKind($diseaseKind);
                $this->entityManager->persist($currentDisease);
            }
            $user->getHealth()->addDisease($currentDisease);
        }
    }
}
