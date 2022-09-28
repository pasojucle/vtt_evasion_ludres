<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class HiddenArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms an string (json) to a array (array).
     *
     * @param string $json
     *
     * @return array
     */
    public function reverseTransform($json): array
    {
        if (null === $json) {
            return [];
        }

        return json_decode($json, true);
    }

    /**
     * Transforms a array (array) to an string (json).
     *
     * @param array $array
     *
     * @return string
     */
    public function transform($array): string
    {
        if (!$array) {
            return '';
        }

        return json_encode($array);
    }
}
