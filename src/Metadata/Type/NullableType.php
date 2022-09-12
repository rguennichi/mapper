<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\ResolverNotFoundException;

/** @internal */
class NullableType extends Type
{
    public function __construct(public readonly TypeInterface $innerType)
    {
    }

    public function __toString(): string
    {
        return '?' . $this->innerType->__toString();
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
