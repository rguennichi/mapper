<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Attribute\Trusted;
use Guennichi\Mapper\Metadata\Model\Argument;

class ArgumentFactory
{
    public function __construct(private readonly ArgumentTypeFactory $typeFactory)
    {
    }

    /**
     * @template T of Attribute
     *
     * @param array<class-string<T>, T> $attributes
     */
    public function __invoke(\ReflectionParameter $reflectionParameter, array $attributes = []): Argument
    {
        foreach ($reflectionParameter->getAttributes(Attribute::class, \ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
            $attributes[$reflectionAttribute->getName()] = $reflectionAttribute->newInstance();
        }

        $isFlexible = isset($attributes[Flexible::class]);

        return new Argument(
            $reflectionParameter->getName(),
            $this->typeFactory->__invoke($reflectionParameter),
            $reflectionParameter->isOptional(),
            $reflectionParameter->isVariadic(),
            !$isFlexible && isset($attributes[Trusted::class]),
            $isFlexible,
            $attributes,
        );
    }
}
