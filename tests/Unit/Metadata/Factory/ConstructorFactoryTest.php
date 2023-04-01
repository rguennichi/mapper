<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Factory;

use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\CustomAttribute;

class ConstructorFactoryTest extends TestCase
{
    public function testItCreatesConstructorCollection(): void
    {
        $factory = new ConstructorFactory(
            $argumentFactory = $this->createMock(ArgumentFactory::class),
        );

        $argumentFactory->expects($this->exactly(1))
            ->method('__invoke')
            ->willReturn(
                $arg = new Argument(
                    'items',
                    $this->createMock(TypeInterface::class),
                    true,
                    true,
                    false,
                    false,
                    [],
                ),
            );

        self::assertEquals(
            new Constructor(Collection::class, ['items' => $arg], new CollectionType(Collection::class, $this->createMock(TypeInterface::class))),
            $factory->__invoke(Collection::class),
        );
    }

    public function testItCreatesConstructorObject(): void
    {
        $factory = new ConstructorFactory(
            $argumentFactory = $this->createMock(ArgumentFactory::class),
        );

        $argument = fn (string $name) => new Argument(
            $name,
            $this->createMock(TypeInterface::class),
            false,
            false,
            false,
            false,
            [CustomAttribute::class => new CustomAttribute()],
        );

        $argumentFactory->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnOnConsecutiveCalls(
                $argument('arg1'),
                $argument('arg2'),
            );

        self::assertEquals(
            new Constructor(
                Example::class,
                ['arg1' => $argument('arg1'), 'arg2' => $argument('arg2')],
                new ObjectType(Example::class),
            ),
            $factory->__invoke(Example::class),
        );
    }
}

class Example
{
    public function __construct(
        public readonly string $arg1,
        public readonly string $arg2,
    ) {
    }
}

/**
 * @implements \IteratorAggregate<string>
 */
class Collection implements \IteratorAggregate
{
    /**
     * @var array<string>
     */
    private readonly array $items;

    public function __construct(string ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return \Traversable<string>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }
}
