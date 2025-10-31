<?php

declare(strict_types=1);

namespace App\UseCase\Licence;

use App\Repository\LicenceRepository;
use App\Service\LicenceService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ExpireLicence
{
    public function __construct(
        private LicenceRepository $licenceRepository,
        private LicenceService $licenceService,
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function execute(): ?array
    {
        $today = new DateTimeImmutable();
        if (1 === (int) $today->format('j') && 1 === (int) $today->format('n')) {
            $lastSeason = $today->format('Y') - 1;
            $licencesExpired = $this->licenceRepository->findAllRegistredFromSeason($lastSeason);
            foreach ($licencesExpired as $licenceExpired) {
                $this->licenceService->applyTransition($licenceExpired, 'expire');
            }
            $this->entityManager->flush();
            return  ['codeError' => 0,  'message' => sprintf('%d licences expired', count($licencesExpired)), ];
        }

        return  ['codeError' => 0,  'message' => 'No licence expired'];
    }
}
