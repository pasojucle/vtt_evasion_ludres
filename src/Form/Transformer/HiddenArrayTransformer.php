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
     * @return string
     */
    public function reverseTransform($json)
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
     * @return array|null
     */
    public function transform($array)
    {
        if (!$array) {
            return '';
        }

        return json_encode($array);
    }
}
