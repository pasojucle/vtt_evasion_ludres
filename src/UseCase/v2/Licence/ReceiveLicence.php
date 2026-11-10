<?php

declare(strict_types=1);

namespace App\UseCase\v2\Licence;

use App\Entity\Licence;
use App\Repository\Interface\LicenceRepositoryInterface;
use App\Service\LicenceService;

class ReceiveLicence
{
    public function __construct(
        private LicenceService $licenceService,
        private LicenceRepositoryInterface $licenceRepository,
    ) {
    }

    public function __invoke(Licence $licence): bool
    {
        $tansition = ($licence->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';

        if ($this->licenceService->applyTransition($licence, $tansition)) {
            $this->licenceRepository->save($licence);
            
            return true;
        }

        return false;
    }
}
