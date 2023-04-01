<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;

/**
 * @template T of object
 *
 * @extends ObjectType<T>
 */
class CollectionType extends ObjectType
{
    /**
     * @param class-string<T> $classname
     */
    public function __construct(string $classname, public readonly TypeInterface $itemType)
    {
        parent::__construct($classname);
    }

    public function resolve(mixed $value, Argument $argument): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
