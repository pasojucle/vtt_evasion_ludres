<?php

declare(strict_types=1);

namespace App\Mapper;

use BackedEnum;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionNamedType;

class FilterMapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function mapToDto(array $data, string $objectClass): object
    {
        $data = array_map(fn ($value) => $value === "" ? null : $value, $data);
        $reflection = new ReflectionClass($objectClass);
        $arguments = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            $rawValue = $data[$name] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);

            if ($rawValue === null || !$type instanceof ReflectionNamedType) {
                $arguments[$name] = $rawValue;
                continue;
            }

            if ($type->isBuiltin()) {
                $arguments[$name] = match ($type->getName()) {
                    'int' => (int) $rawValue,
                    'bool' => filter_var($rawValue, FILTER_VALIDATE_BOOLEAN),
                    default => $rawValue,
                };
                continue;
            }

            $className = $type->getName();
            if ($rawValue instanceof $className) {
                $arguments[$name] = $rawValue;
                continue;
            }

            $arguments[$name] = match (true) {
                $this->isEntity($className) => $this->entityManager->getRepository($className)->find($rawValue),
                is_subclass_of($className, BackedEnum::class) => $className::tryFrom((string) $rawValue),
                default => $rawValue
            }
            ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
        }

        return new $objectClass(...$arguments);
    }

    private function isEntity(string $class): bool
    {
        return !$this->entityManager->getMetadataFactory()->isTransient($class);
    }
}
