<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityHiddenTransformer.
 *
 * @author  Francesco Casula <fra.casula@gmail.com>
 */
class HiddenEntityTransformer implements DataTransformerInterface
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
     * @param object|null $entity
     *
     * @return string
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return '';
        }

        return $entity->getId();
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
}
