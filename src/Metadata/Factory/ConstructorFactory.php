<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\ObjectType;

class ConstructorFactory
{
    public function __construct(private readonly ArgumentFactory $argumentFactory)
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     */
    public function __invoke(string $classname): Constructor
    {
        $reflectionClass = new \ReflectionClass($classname);

        $attributes = [];
        foreach ($reflectionClass->getAttributes(Attribute::class, \ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
            $attributes[$reflectionAttribute->getName()] = $reflectionAttribute->newInstance();
        }

        $arguments = [];
        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            $parameter = $this->argumentFactory->__invoke($reflectionParameter, $attributes);

            $arguments[$parameter->getMappedName()] = $parameter;
        }

        return new Constructor(
            $classname,
            $arguments,
            $this->createType($classname, array_values($arguments)),
        );
    }

    /**
     * @param class-string $classname
     * @param array<Argument> $arguments
     */
    private function createType(string $classname, array $arguments): ObjectType|CollectionType
    {
        if (is_subclass_of($classname, \Traversable::class) && 1 === \count($arguments) && $arguments[0]->variadic) {
            return new CollectionType($classname, $arguments[0]->type);
        }

        return new ObjectType($classname);
    }
}
