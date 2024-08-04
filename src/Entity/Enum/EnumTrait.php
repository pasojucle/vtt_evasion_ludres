<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use ReflectionEnum;
use UnitEnum;

trait EnumTrait
{
    public static function getCases(): array
    {
        $cases = self::cases();

        return array_map(static fn (UnitEnum $case) => $case->name, $cases);
    }
    
    public static function getValues(): array
    {
        $cases = self::cases();

        return array_map(static fn (UnitEnum $case) => $case->value, $cases);
    }

    public static function tryFromCase(?string $case): ?self
    {
        if (null === $case) {
            return null;
        }

        $reflectionEnum = new ReflectionEnum(self::class);

        return $reflectionEnum->hasCase($case) ? $reflectionEnum->getConstant($case) : null;
    }
}
