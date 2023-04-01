<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\ScalarType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;

class Mapper implements MapperInterface
{
    public function __construct(private readonly ConstructorFetcher $constructorFetcher)
    {
    }

    public function __invoke(array $input, string $target): object
    {
        $constructor = $this->constructorFetcher->__invoke($target);

        if ($constructor->type instanceof CollectionType) {
            return $this->resolveCollection(
                $input,
                $target,
                $constructor->type->itemType,
                array_values($constructor->arguments)[0],
            );
        }

        return $this->resolveObject($input, $constructor);
    }

    private function resolve(mixed $input, TypeInterface $type, Argument $argument): mixed
    {
        return match ($type::class) {
            CollectionType::class => $this->resolveCollection($input, $type->classname, $type->itemType, $argument),
            ObjectType::class => $this->resolveObject($input, $this->constructorFetcher->__invoke($type->classname)),
            NullableType::class => $this->resolveNullable($input, $type, $argument),
            ArrayType::class => $this->resolveArray($input, $type, $argument),
            CompoundType::class => $this->resolveCompound($input, $type, $argument),
            DateTimeType::class => $type->resolve($input, $argument),
            default => $argument->trusted ? $input : $type->resolve($input, $argument),
        };
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @return T
     */
    private function resolveCollection(mixed $input, string $classname, TypeInterface $itemType, Argument $argument): object
    {
        if (!\is_array($input)) {
            if ($input instanceof $classname) {
                return $input;
            }

            throw new InvalidTypeException($input, "$classname|array");
        }

        if (!$argument->trusted || !$itemType instanceof ScalarType) {
            foreach ($input as $k => $v) {
                $input[$k] = $this->resolve($v, $itemType, $argument);
            }
        }

        return new $classname(...$input);
    }

    /**
     * @template T of object
     *
     * @param Constructor<T> $constructor
     *
     * @return T
     */
    private function resolveObject(mixed $input, Constructor $constructor): object
    {
        if (!\is_array($input)) {
            if ($input instanceof $constructor->classname) {
                return $input;
            }

            throw new InvalidTypeException($input, "$constructor->classname|array");
        }

        $args = [];
        foreach ($input as $n => $v) {
            if ($argument = $constructor->arguments[$n] ?? false) {
                $args[$argument->name] = $this->resolve($v, $argument->type, $argument);
            }
        }

        return new $constructor->classname(...$args);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function resolveArray(mixed $input, ArrayType $type, Argument $argument): array
    {
        if (!\is_array($input)) {
            throw new InvalidTypeException($input, 'array');
        }

        $vt = $type->valueType;
        if (!$argument->trusted || !$vt instanceof ScalarType) {
            foreach ($input as $k => $v) {
                $input[$k] = $this->resolve($v, $vt, $argument);
            }
        }

        return $input;
    }

    private function resolveCompound(mixed $input, CompoundType $type, Argument $argument): mixed
    {
        foreach ($type->types as $t) {
            try {
                return $this->resolve($input, $t, $argument);
            } catch (InvalidTypeException) {
            }
        }

        throw new InvalidTypeException($input, implode(', ', array_map(get_class(...), $type->types)));
    }

    private function resolveNullable(mixed $input, NullableType $type, Argument $argument): mixed
    {
        return $input ? $this->resolve($input, $type->innerType, $argument) : null;
    }
}
