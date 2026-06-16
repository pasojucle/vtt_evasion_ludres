<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class HiddenChoiceMultipleTransformer implements DataTransformerInterface
{
    /**
     * Convertit le tableau PHP en chaîne JSON pour l'attribut HTML value
     */
    public function transform($value): string
    {
        if (empty($value)) {
            return '';
        }

        return json_encode(array_values((array) $value));
    }

    /**
     * Intercepte la string JSON ou le tableau soumis pour renvoyer un tableau PHP propre
     */
    public function reverseTransform($submittedValue): array
    {
        if (null === $submittedValue || '' === $submittedValue || [] === $submittedValue) {
            return [];
        }

        if (is_string($submittedValue)) {
            $rawValues = json_decode($submittedValue, true);
            return is_array($rawValues) ? $rawValues : [];
        }

        return (array) $submittedValue;
    }
}
