<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use App\Model\Money;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<Money, float>
 */
class MoneyTransformer implements DataTransformerInterface
{
    /**     *
     * @param Money|mixed $value
     */
    public function transform(mixed $value): ?float
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new TransformationFailedException('Expected an instance of App\Model\Money.');
        }

        return $value->getAmount();
    }

    /**
     *
     * @param float|int|string|mixed $value
     */
    public function reverseTransform(mixed $value): ?Money
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException('Expected a numeric value.');
        }

        return Money::fromFloat((float) $value);
    }
}
