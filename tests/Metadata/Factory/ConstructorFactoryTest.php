<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Factory;

use Guennichi\Collection\Collection;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\ParameterFactory;
use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConstructorFactoryTest extends TestCase
{
    private ConstructorFactory $constructorFactory;
    private ParameterFactory&MockObject $parameterFactory;

    protected function setUp(): void
    {
        $this->constructorFactory = new ConstructorFactory(
            $this->parameterFactory = $this->createMock(ParameterFactory::class),
        );
    }

    public function testItCreatesConstructorObjectFromClassname(): void
    {
        $this->parameterFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $paramOne = new Parameter(
                    'paramOne',
                    $this->createMock(TypeInterface::class),
                    true,
                    [],
                ),
                $paramTwo = new Parameter(
                    'paramTwo',
                    $this->createMock(TypeInterface::class),
                    true,
                    [],
                ),
            );

        self::assertEquals(
            new Constructor(SimpleObject::class, new ObjectType(SimpleObject::class), [
                'paramOne' => $paramOne,
                'paramTwo' => $paramTwo,
            ]),
            $this->constructorFactory->create(SimpleObject::class),
        );
    }

    public function testItCreatesConstructorCollectionFromClassname(): void
    {
        $this->parameterFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $paramOne = new Parameter(
                    'elements',
                    $this->createMock(TypeInterface::class),
                    true,
                    [],
                ),
            );

        self::assertEquals(
            new Constructor(CollectionWithOneArgument::class, new CollectionType(CollectionWithOneArgument::class, $paramOne->type), [
                'elements' => $paramOne,
            ]),
            $this->constructorFactory->create(CollectionWithOneArgument::class),
        );
    }

    public function testItFailsToCreatesConstructorCollectionFromClassnameWithMultipleParameters(): void
    {
        $this->parameterFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                new Parameter(
                    'param1',
                    $this->createMock(TypeInterface::class),
                    true,
                    [],
                ),
                new Parameter(
                    'param2',
                    $this->createMock(TypeInterface::class),
                    true,
                    [],
                ),
            );

        self::expectExceptionMessage('Collection should have exactly one parameter in constructor in order to know its value type, "2" given');

        $this->constructorFactory->create(CollectionWithTwoArguments::class);
    }
}

class SimpleObject
{
    public function __construct(
        public readonly string $paramOne,
        public readonly int $paramTwo,
    ) {
    }
}

/**
 * @extends Collection<string>
 */
class CollectionWithOneArgument extends Collection
{
    public function __construct(string ...$elements)
    {
        parent::__construct(...$elements);
    }
}

/**
 * @extends Collection<string>
 */
class CollectionWithTwoArguments extends Collection
{
    public function __construct(public readonly string $param1, public readonly string $param2)
    {
        parent::__construct();
    }
}
