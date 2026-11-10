<?php

declare(strict_types=1);

namespace App\UseCase\v2\Parameter;

use App\Entity\Parameter;
use App\Repository\Interface\ParameterRepositoryInterface;
use App\Service\ParameterService;

class UpdateParameter
{
    public function __construct(
        private ParameterRepositoryInterface $parameterRepository,
        private ParameterService $parameterService,
    ) {
    }

    public function __invoke(Parameter $parameter): void
    {
        $this->parameterRepository->save($parameter);
        $this->parameterService->updateParamer($parameter);
    }
}
