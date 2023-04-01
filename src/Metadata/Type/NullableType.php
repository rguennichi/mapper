<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;

class NullableType implements TypeInterface
{
    public function __construct(public readonly TypeInterface $innerType)
    {
    }

    public function resolve(mixed $value, Argument $argument): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
