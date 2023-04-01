<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;

/**
 * @template T of object
 */
class ObjectType implements TypeInterface
{
    /**
     * @param class-string<T> $classname
     */
    public function __construct(public readonly string $classname)
    {
    }

    public function resolve(mixed $value, Argument $argument): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
