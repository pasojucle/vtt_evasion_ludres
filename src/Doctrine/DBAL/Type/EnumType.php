<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class EnumType extends Type
{
    /**
     * @return class-string
     */
    abstract protected function getEnum(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        $enum = $this->getEnum();
        $cases = array_map(
            fn ($enumItem) => "'{$enumItem->value}'",
            $enum::cases()
        );

        return sprintf('ENUM(%s)', implode(', ', $cases));
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $enumClass = $this->getEnum();

        return $enumClass::from($value);
    }

    public function convertToDatabaseValue($enum, AbstractPlatform $platform)
    {
        return $enum->value;
    }
}
