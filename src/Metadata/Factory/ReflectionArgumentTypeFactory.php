<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Exception\MapperException;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BackedEnumType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\FalseType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TrueType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use Guennichi\Mapper\Metadata\Util\ClassnameDateTimeTypeChecker;
use Guennichi\Mapper\Metadata\Util\CollectionReflectionParameterFetcher;

class ReflectionArgumentTypeFactory implements ArgumentTypeFactoryInterface
{
    public function __invoke(\ReflectionParameter $reflectionParameter): TypeInterface
    {
        return $this->createFromReflectionType(
            $reflectionParameter->getType(),
            $reflectionParameter->getDeclaringClass()?->getName(),
            $reflectionParameter->getName(),
        );
    }

    private function createFromReflectionType(?\ReflectionType $reflectionType, ?string $classname, string $argument): TypeInterface
    {
        if ($reflectionType instanceof \ReflectionUnionType) {
            $types = $reflectionType->getTypes();
            if (\count($types) < 2) {
                throw MapperException::createFromClassnameArgument(sprintf('Compound type should at least have two types, "%d" type found.', \count($types)), $classname, $argument);
            }

            return new CompoundType(
                array_map(
                    fn (\ReflectionType $type) => $this->createFromReflectionType($type, $classname, $argument),
                    $types,
                ),
            );
        }

        if (!$reflectionType instanceof \ReflectionNamedType) {
            throw MapperException::createFromClassnameArgument(sprintf('Type "%s" is not yet supported', $reflectionType ? $reflectionType::class : 'null'), $classname, $argument);
        }

        $literalType = $reflectionType->getName();
        if ($reflectionType->isBuiltin()) {
            $type = match ($literalType) {
                'string' => new StringType(),
                'int' => new IntegerType(),
                'bool' => new BooleanType(),
                'float' => new FloatType(),
                'null' => new NullType(),
                'true' => new TrueType(),
                'false' => new FalseType(),
                'array' => new ArrayType(
                    new CompoundType([
                        new StringType(),
                        new IntegerType(),
                    ]),
                    new MixedType(),
                ),
                'mixed' => new MixedType(),
                default => throw MapperException::createFromClassnameArgument(sprintf('Type "%s" is not yet supported', $literalType), $classname, $argument),
            };
        } elseif ($reflection = CollectionReflectionParameterFetcher::tryFetch($reflectionType)) {
            \assert(is_subclass_of($literalType, \Traversable::class));

            $type = new CollectionType(
                $literalType,
                $this->createFromReflectionType($reflection->getType(), $classname, $argument),
            );
        } elseif (ClassnameDateTimeTypeChecker::isDateTime($literalType)) {
            \assert(\DateTimeInterface::class === $literalType || is_subclass_of($literalType, \DateTimeInterface::class));

            $type = new DateTimeType($literalType);
        } elseif (is_subclass_of($literalType, \BackedEnum::class)) {
            $backingType = $this->createFromReflectionType((new \ReflectionEnum($literalType))->getBackingType(), $classname, $argument);
            if (!$backingType instanceof StringType && !$backingType instanceof IntegerType) {
                throw MapperException::createFromClassnameArgument(sprintf('"%s" backing type is not supported', $backingType::class), $classname, $argument);
            }

            $type = new BackedEnumType($literalType, $backingType);
        } else {
            \assert(class_exists($literalType));

            $type = new ObjectType($literalType);
        }

        return $reflectionType->allowsNull()
        && !$type instanceof NullType
        && !$type instanceof MixedType ?
            new NullableType($type) :
            $type;
    }
}
