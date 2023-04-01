<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;

class CompoundType implements TypeInterface
{
    /**
     * @param non-empty-array<TypeInterface> $types
     */
    public function __construct(public readonly array $types)
    {
    }

    public function resolve(mixed $value, Argument $argument): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
