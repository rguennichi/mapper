<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory\Type;

use Guennichi\Mapper\Metadata\Factory\Type\Source\ParameterTypeFactoryInterface;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\ListType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use ReflectionParameter;
use RuntimeException;

class ParameterTypeFactory
{
    public function __construct(
        private readonly ParameterTypeFactoryInterface $reflectionTypeFactory,
        private readonly ParameterTypeFactoryInterface $docBlockTypeFactory,
    ) {
    }

    public function create(ReflectionParameter $reflectionParameter): TypeInterface
    {
        $type = $this->reflectionTypeFactory->create($reflectionParameter);

        if ($this->requiresDocBlock($type)) {
            $type = $this->docBlockTypeFactory->create($reflectionParameter);
        }

        if (null === $type) {
            throw new \RuntimeException(sprintf('Unable to extract type in "%s::%s"', $reflectionParameter->getDeclaringClass()?->getName(), $reflectionParameter->getName()));
        }

        return $type;
    }

    private function requiresDocBlock(?TypeInterface $type): bool
    {
        if ($type instanceof NullableType) {
            return $this->requiresDocBlock($type->innerType);
        }

        if ($type instanceof CompoundType) {
            foreach ($type->types as $innerType) {
                if (!$this->requiresDocBlock($innerType)) {
                    continue;
                }

                return true;
            }
        }

        return $type instanceof ListType;
    }
}
