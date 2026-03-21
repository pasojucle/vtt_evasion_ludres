<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityHiddenTransformer.
 *
 * @author  Francesco Casula <fra.casula@gmail.com>
 */
class HiddenEnumTransformer implements DataTransformerInterface
{
    private $enumClass;

    public function __construct($enumClass)
    {
        $this->enumClass = $enumClass;
    }

    /**
     * Transforms an object (v) to a string.
     *
     * @param object|null $enum
     *
     * @return string
     */
    public function transform($enum): ?string
    {
        if (null === $enum) {
            return null;
        }

        return $enum->value;
    }

    /**
     * Transforms a string to an object (enum).
     *
     * @param string $value
     *
     * @throws TransformationFailedException if object (enum) is not found
     *
     * @return object|null
     */
    public function reverseTransform($value): ?object
    {
        if (!$value) {
            return null;
        }
        
        $enum = $this->enumClass::tryFrom($value);
        if (null === $enum) {
            throw new TransformationFailedException(sprintf(
                'La valeur "%s" n\'est pas valide pour l\'Enum %s',
                $value,
                $this->enumClass
            ));
        }

        return $enum;
    }
}
