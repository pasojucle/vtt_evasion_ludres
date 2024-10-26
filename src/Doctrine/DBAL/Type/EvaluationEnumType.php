<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\EvaluationEnum;

class EvaluationEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return EvaluationEnum::class;
    }

    public function getName(): string
    {
        return 'Evaluation';
    }
}
