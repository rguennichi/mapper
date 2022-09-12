<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Collection\CollectionInterface;
use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use ReflectionMethod;
use RuntimeException;

class ConstructorFactory
{
    public function __construct(private readonly ParameterFactory $parameterFactory)
    {
    }

    public function create(string $classname): Constructor
    {
        $reflectionConstructor = new ReflectionMethod($classname, '__construct');

        $parameters = [];
        foreach ($reflectionConstructor->getParameters() as $reflectionParameter) {
            $parameters[$reflectionParameter->getName()] = $this->parameterFactory->create($reflectionParameter);
        }

        return new Constructor($classname, $this->getClassType($classname, array_values($parameters)), $parameters);
    }

    /**
     * @param array<int, Parameter> $parameters
     */
    private function getClassType(string $classname, array $parameters): ObjectType|CollectionType
    {
        if (!\in_array(CollectionInterface::class, class_implements($classname) ?: [])) {
            return new ObjectType($classname);
        }

        if (1 !== \count($parameters)) {
            throw new RuntimeException(sprintf('Collection should have exactly one parameter in constructor in order to know its value type, "%d" given', \count($parameters)));
        }

        return new CollectionType($classname, $parameters[0]->type);
    }
}
