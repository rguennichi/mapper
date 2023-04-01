<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

/**
 * @template T of object
 *
 * @implements \IteratorAggregate<T>
 */
abstract class Collection implements \IteratorAggregate
{
    /**
     * @param array<T> $collection
     */
    public function __construct(public readonly array $collection)
    {
    }

    public function getIterator(): \Traversable
    {
        yield from $this->collection;
    }
}
