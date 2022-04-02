<?php

declare(strict_types=1);

namespace App\UseCase\Coverage;

use App\Entity\Licence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ValidateCoverage
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function execute(Request $request, Licence $licence)
    {
        $licence->setCurrentSeasonForm(true);
        $this->entityManager->persist($licence);

        $this->entityManager->flush();
    }
}
