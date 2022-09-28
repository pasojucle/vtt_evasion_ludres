<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use App\Entity\BikeRide;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use IntlDateFormatter;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BikeRideTransformer implements DataTransformerInterface
{
    private $entityClass;

    public function __construct(private ObjectManager $objectManager, $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Transforms an object (entity) to a string (number).
     *
     * @param object|null $bikeRide
     *
     * @return array|string
     */
    public function transform($bikeRide): array|string
    {
        if (null === $bikeRide) {
            return '';
        }

        return [$bikeRide->getId() => $this->getPeriod($bikeRide) . ' - ' . $bikeRide->getTitle()];
    }

    /**
     * Transforms a string (number) to an object (entity).
     *
     * @param string $identifier
     *
     * @throws TransformationFailedException if object (entity) is not found
     *
     * @return object|null
     */
    public function reverseTransform($identifier)
    {
        if (!$identifier) {
            return null;
        }

        $entity = $this->getObjectManager()
            ->getRepository($this->entityClass)
            ->find($identifier)
        ;

        if (null === $entity) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(\sprintf('An entity with ID "%s" does not exist!', $identifier));
        }

        return $entity;
    }

    private function formatDateLong(DateTimeImmutable $date): string
    {
        $formatter = new IntlDateFormatter('fr_fr', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE d/M/yy');

        return ucfirst($formatter->format($date));
    }

    private function getPeriod(BikeRide $bikeRide): string
    {
        return  (null === $bikeRide->getEndAt())
        ? $this->formatDateLong($bikeRide->getStartAt())
        : $this->formatDateLong($bikeRide->getStartAt()) . ' au ' . $this->formatDateLong($bikeRide->getEndAt());
    }
}
