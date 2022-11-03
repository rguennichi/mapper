<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Metadata\Factory\Type\ParameterTypeFactory;
use Guennichi\Mapper\Metadata\Member\Parameter;

class ParameterFactory
{
    public function __construct(private readonly ParameterTypeFactory $parameterTypeFactory)
    {
    }

    public function create(\ReflectionParameter $reflectionParameter): Parameter
    {
        $attributes = [];
        foreach ($reflectionParameter->getAttributes(Attribute::class, \ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
            $attributes[$reflectionAttribute->getName()] = $reflectionAttribute->newInstance();
        }

        return new Parameter(
            $reflectionParameter->getName(),
            $this->parameterTypeFactory->create($reflectionParameter),
            !$reflectionParameter->isOptional(),
            $attributes,
        );
    }
}
