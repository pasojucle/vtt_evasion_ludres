<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use DateTimeImmutable;
use Symfony\Component\Form\DataTransformerInterface;

class HiddenDateTransformer implements DataTransformerInterface
{
    /**
     * Transforms an Date (v) to a string.
     *
     * @param DateTimeImmutable|null $dateTime
     *
     * @return string
     */
    public function transform($dateTime): ?string
    {
        if (null === $dateTime) {
            return null;
        }

        return $dateTime->format('Y-m-d');
    }

    /**
     * Transforms a string to an Date.
     *
     * @param string $value
     *     *
     * @return DateTimeImmutable|null
     */
    public function reverseTransform($value): ?DateTimeImmutable
    {
        if (!$value) {
            return null;
        }

        return new DateTimeImmutable($value);
    }
}
