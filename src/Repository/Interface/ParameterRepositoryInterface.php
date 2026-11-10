<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Parameter;

interface ParameterRepositoryInterface
{
    public function save(Parameter $parameter, bool $flush = true): void;

    public function remove(Parameter $parameter, bool $flush = true): void;
}
