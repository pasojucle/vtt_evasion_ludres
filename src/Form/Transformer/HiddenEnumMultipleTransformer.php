<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class HiddenEnumMultipleTransformer implements DataTransformerInterface
{
    private $enumClass;

    public function __construct($enumClass)
    {
        $this->enumClass = $enumClass;
    }

    /**
     * Transforms an string[] to a enum[] (array).
     *
     * @param array $values
     *
     * @return array
     */
    public function reverseTransform($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_map(fn ($enum) => $this->tryFrom($enum), $values);
    }

    private function tryFrom(string $value)
    {
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

    /**
     * Transforms a array (array) to an string (json).
     *
     * @param array $enums
     *
     * @return string
     */
    public function transform($enums): string
    {
        if (!$enums) {
            return '';
        }
        
        return json_encode(array_map(fn ($enum) => $enum->value, $enums));
    }
}
