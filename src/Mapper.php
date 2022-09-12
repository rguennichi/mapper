<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

use Guennichi\Mapper\Exception\MissingArgumentsException;
use Guennichi\Mapper\Exception\UnexpectedValueException;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\ParameterFactory;
use Guennichi\Mapper\Metadata\Factory\Type\ParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\ParameterTypeFactoryInterface;
use Guennichi\Mapper\Metadata\Factory\Type\Source\PhpDocumentorParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\ReflectionParameterTypeFactory;
use Guennichi\Mapper\Metadata\Repository\ConstructorInMemoryRepository;
use Guennichi\Mapper\Metadata\Repository\ConstructorRepositoryInterface;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;

class Mapper implements MapperInterface
{
    private readonly ConstructorFetcher $constructorFetcher;

    /**
     * @param iterable<ResolverInterface> $resolvers
     */
    public function __construct(
        ConstructorRepositoryInterface $constructorRepository = new ConstructorInMemoryRepository(),
        ParameterTypeFactoryInterface $phpDocParameterTypeFactory = new PhpDocumentorParameterTypeFactory(),
        ParameterTypeFactoryInterface $reflectionParameterTypeFactory = new ReflectionParameterTypeFactory(),
        ?ConstructorFetcher $constructorFetcher = null,
        private readonly iterable $resolvers = [],
    ) {
        $this->constructorFetcher = $constructorFetcher ?? new ConstructorFetcher(
            new ConstructorFactory(
                new ParameterFactory(
                    new ParameterTypeFactory(
                        $reflectionParameterTypeFactory,
                        $phpDocParameterTypeFactory,
                    ),
                ),
            ),
            $constructorRepository,
        );
    }

    public function map(mixed $source, string $target): object
    {
        /* @phpstan-ignore-next-line */
        return $this->resolve(
            $source,
            $this->constructorFetcher->fetch($target)->classType,
            new Context($target),
        );
    }

    private function resolve(mixed $input, TypeInterface $type, Context $context): mixed
    {
        // Prioritize custom resolvers
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->supports($type)) {
                continue;
            }

            return $resolver->resolve($input, $type, $context);
        }

        return match ($type::class) {
            default => $type->resolve($input, $context),
            ObjectType::class => $this->resolveObject($input, $type, $context),
            NullableType::class => $this->resolveNullable($input, $type, $context),
            ArrayType::class => $this->resolveArray($input, $type, $context),
            CollectionType::class => $this->resolveCollection($input, $type, $context),
            CompoundType::class => $this->resolveCompound($input, $type, $context),
        };
    }

    private function resolveNullable(mixed $input, NullableType $type, Context $context): mixed
    {
        if (null === $input) {
            return null;
        }

        return $this->resolve($input, $type->innerType, $context);
    }

    /**
     * Try to AVOID this type as much as possible.
     */
    private function resolveCompound(mixed $input, CompoundType $type, Context $context): mixed
    {
        foreach ($type->types as $possibleType) {
            try {
                return $this->resolve($input, $possibleType, $context);
            } catch (UnexpectedValueException) {
            }
        }

        throw new UnexpectedValueException($input, $type->__toString(), $context);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function resolveArray(mixed $input, ArrayType $type, Context $context): array
    {
        if (!\is_array($input)) {
            throw new UnexpectedValueException($input, 'array', $context);
        }

        $items = [];
        foreach ($input as $key => $value) {
            $items[$type->keyType->resolve($key, $context)] = $this->resolve($value, $type->valueType, $context);
        }

        return $items;
    }

    private function resolveCollection(mixed $input, CollectionType $type, Context $context): object
    {
        if ($input instanceof $type->classname) {
            return $input;
        }

        $context->visitClassname($type->classname);

        if (!\is_array($input)) {
            throw new UnexpectedValueException($input, 'array', $context);
        }

        $items = [];
        foreach ($input as $element) {
            $items[] = $this->resolve($element, $type->valueType, $context);
        }

        return new $type->classname(...$items);
    }

    private function resolveObject(mixed $input, ObjectType $type, Context $context): object
    {
        if ($input instanceof $type->classname) {
            return $input;
        }

        $context->visitClassname($type->classname);

        if (!\is_array($input)) {
            throw new UnexpectedValueException($input, 'array', $context);
        }

        $constructor = $this->constructorFetcher->fetch($type->classname);

        // Early failure in case array keys does not match the required constructor params
        if ($missingParameters = array_diff_key($constructor->requiredParameters, $input)) {
            throw new MissingArgumentsException($constructor->classname, array_keys($missingParameters), $input, $context);
        }

        $arguments = [];
        foreach ($input as $name => $value) {
            if (!($parameter = $constructor->parameters[$name] ?? null)) {
                continue;
            }

            $context->visitParameter($parameter);

            $arguments[$name] = $this->resolve($value, $parameter->type, $context);
        }

        return new $constructor->classname(...$arguments);
    }
}
