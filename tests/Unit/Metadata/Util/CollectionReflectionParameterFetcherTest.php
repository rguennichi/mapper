<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Util;

use Guennichi\Mapper\Metadata\Util\CollectionReflectionParameterFetcher;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\Collection;

class CollectionReflectionParameterFetcherTest extends TestCase
{
    public function testItReturnsNullIfTypeIsNotCollection(): void
    {
        self::assertNull(CollectionReflectionParameterFetcher::tryFetch(null));

        self::assertNull(CollectionReflectionParameterFetcher::tryFetch($this->createMock(\ReflectionUnionType::class)));
        self::assertNull(CollectionReflectionParameterFetcher::tryFetch($this->createMock(\ReflectionIntersectionType::class)));

        self::assertNull(CollectionReflectionParameterFetcher::tryFetch($this->type('nonTraversableObject')));
        self::assertNull(CollectionReflectionParameterFetcher::tryFetch($this->type('builtInArgument')));
        self::assertNull(CollectionReflectionParameterFetcher::tryFetch($this->type('collectionWithTwoArguments')));
    }

    public function testItReturnsReflectionParameter(): void
    {
        self::assertInstanceOf(
            \ReflectionParameter::class,
            CollectionReflectionParameterFetcher::tryFetch($this->type('collectionWithArrayArgument')),
        );
        self::assertInstanceOf(
            \ReflectionParameter::class,
            CollectionReflectionParameterFetcher::tryFetch($this->type('collectionWithVariadicArgument')),
        );
    }

    private function type(string $argument): ?\ReflectionType
    {
        return (new \ReflectionParameter([new Collections(), '__construct'], $argument))->getType();
    }
}

/**
 * @extends Collection<\stdClass>
 */
class CollectionWithTwoArguments extends Collection
{
    /**
     * @param array<\stdClass> $collection
     */
    public function __construct(array $collection, public readonly int $additionalParameter)
    {
        parent::__construct($collection);
    }
}

/**
 * @extends Collection<\stdClass>
 */
class CollectionWithArrayArgument extends Collection
{
}

/**
 * @extends Collection<\stdClass>
 */
class CollectionWithVariadicArgument extends Collection
{
    public function __construct(\stdClass ...$objects)
    {
        parent::__construct($objects);
    }
}

class Collections
{
    public function __construct(
        public readonly \stdClass $nonTraversableObject = new \stdClass(),
        public readonly string $builtInArgument = '',
        public readonly CollectionWithTwoArguments $collectionWithTwoArguments = new CollectionWithTwoArguments([], 1),
        public readonly CollectionWithArrayArgument $collectionWithArrayArgument = new CollectionWithArrayArgument([]),
        public readonly CollectionWithVariadicArgument $collectionWithVariadicArgument = new CollectionWithVariadicArgument(),
    ) {
    }
}
