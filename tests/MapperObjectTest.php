<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper;

use Guennichi\Mapper\Exception\MissingArgumentsException;
use Guennichi\Mapper\Exception\UnexpectedTypeException;
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\MapperInterface;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MapperObjectTest extends TestCase
{
    private MapperInterface $mapper;
    private ConstructorFetcher&MockObject $constructorFetcher;

    protected function setUp(): void
    {
        $this->mapper = new Mapper(
            $this->constructorFetcher = $this->createMock(ConstructorFetcher::class),
            [],
        );
    }

    public function testItThrowsExceptionWithInvalidInputType(): void
    {
        $expectedConstructor = new Constructor(
            SimpleObject::class,
            new ObjectType(SimpleObject::class),
            [
                'paramOne' => new Parameter(
                    'paramOne',
                    new StringType(),
                    true,
                    [],
                ),
            ],
        );

        $this->constructorFetcher->expects(self::once())
            ->method('fetch')
            ->with(SimpleObject::class)
            ->willReturn($expectedConstructor);

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "array", "string" given');

        $this->mapper->map('string_value', SimpleObject::class);
    }

    public function testItThrowsExceptionWithIncompleteArrayInput(): void
    {
        $expectedConstructor = new Constructor(
            SimpleObject::class,
            new ObjectType(SimpleObject::class),
            [
                'paramOne' => new Parameter(
                    'paramOne',
                    new StringType(),
                    true,
                    [],
                ),
            ],
        );

        $this->constructorFetcher->expects(self::exactly(2))
            ->method('fetch')
            ->withConsecutive(
                [SimpleObject::class],
                [SimpleObject::class],
            )
            ->willReturnOnConsecutiveCalls(
                $expectedConstructor,
                $expectedConstructor,
            );

        $this->expectException(MissingArgumentsException::class);
        $this->expectExceptionMessage('Missing arguments "paramOne" in "Tests\Guennichi\Mapper\SimpleObject::__construct"');

        $this->mapper->map(['unknownParam' => 'test'], SimpleObject::class);
    }

    public function testItReturnsSameObjectWithValidObjectInput(): void
    {
        $expectedConstructor = new Constructor(
            SimpleObject::class,
            new ObjectType(SimpleObject::class),
            [
                'paramOne' => new Parameter(
                    'paramOne',
                    new StringType(),
                    true,
                    [],
                ),
            ],
        );

        $this->constructorFetcher->expects(self::once())
            ->method('fetch')
            ->with(SimpleObject::class)
            ->willReturn($expectedConstructor);

        self::assertEquals(
            new SimpleObject('test1'),
            $this->mapper->map(new SimpleObject('test1'), SimpleObject::class),
        );
    }

    public function testItReturnsObjectWithValidArrayInput(): void
    {
        $expectedConstructor = new Constructor(
            SimpleObject::class,
            new ObjectType(SimpleObject::class),
            [
                'paramOne' => new Parameter(
                    'paramOne',
                    new StringType(),
                    true,
                    [],
                ),
            ],
        );

        $this->constructorFetcher->expects(self::exactly(2))
            ->method('fetch')
            ->withConsecutive(
                [SimpleObject::class],
                [SimpleObject::class],
            )
            ->willReturnOnConsecutiveCalls(
                $expectedConstructor,
                $expectedConstructor,
            );

        self::assertEquals(
            new SimpleObject('test'),
            $this->mapper->map(['paramOne' => 'test'], SimpleObject::class),
        );
    }
}

class SimpleObject
{
    public function __construct(public readonly string $paramOne)
    {
    }
}
