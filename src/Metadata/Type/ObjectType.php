<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\ResolverNotFoundException;

/** @internal */
class ObjectType extends Type
{
    public function __construct(public readonly string $classname)
    {
    }

    public function __toString(): string
    {
        return $this->classname;
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
