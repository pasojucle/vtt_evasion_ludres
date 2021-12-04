<?php

namespace App\Service;

use App\Entity\Parameter;
use App\Repository\ParameterRepository;


class ParameterService
{
    private ParameterRepository $parameterRepository;
    public function __construct(ParameterRepository $parameterRepository)
    {
        $this->parameterRepository = $parameterRepository;
    }

    public function getParameterByName(string $name)
    {
        $parameter = $this->parameterRepository->findOneByName($name);

        $value = null;
        if ($parameter) {
            $value = $parameter->getValue();
        }

        return $value;
    }
}