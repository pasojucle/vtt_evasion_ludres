<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Filter\AbstractFilter;
use BackedEnum;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class FilterMapper
{
    private DocBlockFactoryInterface $docBlockFactory;
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    public function mapToDto(array $data, string $objectClass): AbstractFilter
    {
        $data = array_map(fn ($value) => $value === "" ? null : $value, $data);
        $reflection = new ReflectionClass($objectClass);
        $arguments = [];

        $contextFactory = new ContextFactory();
        $context = $contextFactory->createFromReflector($reflection);

        $constructor = $reflection->getConstructor();

        $docBlock = $constructor && $constructor->getDocComment() 
            ? $this->docBlockFactory->create($constructor->getDocComment(), $context) 
            : null;

        /** @var ReflectionParameter $parameter*/
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            $rawValue = $data[$name] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);

            if ($rawValue === null || !$type instanceof ReflectionNamedType) {
                $arguments[$name] = $rawValue;
                continue;
            }

            if ($type->getName() === 'array' && is_array($rawValue)) {
                $arguments[$name] = $this->mapArrayValue($rawValue, $name, $docBlock);
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

            $arguments[$name] = $this->mapSingleValue($className, $rawValue) 
                ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
        }

        return new $objectClass(...$arguments);
    }

    private function mapSingleValue(string $className, mixed $value): mixed
    {

        return match (true) {
            $this->isEntity($className) => $this->entityManager->getRepository($className)->find($value),
            is_subclass_of($className, BackedEnum::class) => $className::tryFrom((string) $value),
            default => $value
        };
    }

    private function mapArrayValue(array $values, string $paramName, ?DocBlock $docBlock): array
{
    if (!$docBlock) {
        return $values;
    }

    /** @var Param $paramTag */
    foreach ($docBlock->getTagsByName('param') as $paramTag) {
        if ($paramTag->getVariableName() === $paramName) {
            $resolvedType = $paramTag->getType();
            $arrayType = null;
            if ($resolvedType instanceof Nullable) {
                $realType = $resolvedType->getActualType();
                if ($realType instanceof Array_) {
                    $arrayType = $realType;
                }
            } elseif ($resolvedType instanceof Compound) {
                foreach ($resolvedType as $subType) {
                    if ($subType instanceof Array_) {
                        $arrayType = $subType;
                        break;
                    }
                }
            } elseif ($resolvedType instanceof Array_) {
                $arrayType = $resolvedType;
            }
            if ($arrayType !== null) {
                $valueType = $arrayType->getValueType();
                $targetClass = ltrim((string) $valueType, '\\');
                if (class_exists($targetClass)) {
                    return array_filter(array_map(
                        fn ($item) => $this->mapSingleValue($targetClass, $item),
                        $values
                    ));
                }
            }
            break;
        }
    }

    return $values;
}
    private function isEntity(string $class): bool
    {
        return !$this->entityManager->getMetadataFactory()->isTransient($class);
    }
}
