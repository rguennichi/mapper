<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory\Type\Source;

use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;

class ReflectionParameterTypeFactory implements ParameterTypeFactoryInterface
{
    public function create(\ReflectionParameter $reflectionParameter): ?TypeInterface
    {
        return $this->createType($reflectionParameter->getType());
    }

    private function createType(?\ReflectionType $reflectionType): TypeInterface
    {
        if ($reflectionType instanceof \ReflectionUnionType) {
            return new CompoundType(...array_map($this->createType(...), $reflectionType->getTypes()));
        }

        if (!$reflectionType instanceof \ReflectionNamedType) {
            throw new \RuntimeException(sprintf('Type "%s" is not supported', $reflectionType ? $reflectionType::class : 'unknown'));
        }

        if ($reflectionType->isBuiltin()) {
            $type = match ($reflectionType->getName()) {
                'string' => new StringType(),
                'int' => new IntegerType(),
                'bool' => new BooleanType(),
                'float' => new FloatType(),
                'null' => new NullType(),
                'array' => new ArrayType(new IntegerType(), new MixedType()),
                default => throw new \RuntimeException(sprintf('Type "%s" is not supported', $reflectionType->getName())),
            };
        } else {
            // Here maybe we need to check if the class is instance of some collection interface, and we could extract its information from @template tag.
            if (is_subclass_of($reflectionType->getName(), \Traversable::class)) {
                $type = new CollectionType($reflectionType->getName(), new MixedType());
            } elseif (\DateTimeInterface::class === $reflectionType->getName() || is_subclass_of($reflectionType->getName(), \DateTimeInterface::class)) {
                $type = new DateTimeType($reflectionType->getName());
            } else {
                $type = new ObjectType($reflectionType->getName());
            }
        }

        return $reflectionType->allowsNull() && !$type instanceof NullType ? new NullableType($type) : $type;
    }
}
