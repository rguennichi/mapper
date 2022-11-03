<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Factory\Type;

use Guennichi\Mapper\Metadata\Factory\Type\ParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\ParameterTypeFactoryInterface;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParameterTypeFactoryTest extends TestCase
{
    private ParameterTypeFactory $parameterTypeFactory;
    private ParameterTypeFactoryInterface&MockObject $reflectionTypeFactory;
    private ParameterTypeFactoryInterface&MockObject $docBlockTypeFactory;

    protected function setUp(): void
    {
        $this->parameterTypeFactory = new ParameterTypeFactory(
            $this->reflectionTypeFactory = $this->createMock(ParameterTypeFactoryInterface::class),
            $this->docBlockTypeFactory = $this->createMock(ParameterTypeFactoryInterface::class),
        );
    }

    /**
     * @dataProvider phpDocFactoryDataProvider
     */
    public function testItCreatesTypeThatRequiresDocBlockFactory(TypeInterface $type): void
    {
        $this->reflectionTypeFactory->expects($this->once())
            ->method('create')
            ->willReturn($type);

        $this->docBlockTypeFactory->expects($this->once())
            ->method('create')
            ->willReturn($docBlockFactoryResult = clone $type);

        self::assertSame($docBlockFactoryResult, $this->parameterTypeFactory->create($this->createMock(\ReflectionParameter::class)));
    }

    public function testItThrowsWhenUnableToExtractType(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Unable to extract type in "class@anonymous');
        $unguessable = new class([]) {
            /** @phpstan-ignore-next-line  */
            public function __construct(public array $array)
            {
            }
        };

        $this->parameterTypeFactory->create(new \ReflectionParameter($unguessable->__construct(...), 'array'));
    }

    /**
     * @return array<array{TypeInterface&MockObject}|array{TypeInterface}>
     */
    public function phpDocFactoryDataProvider(): array
    {
        return [
            [$this->createMock(ArrayType::class)],
            [$this->createMock(CollectionType::class)],
            [new NullableType($this->createMock(ArrayType::class))],
            [new CompoundType(
                $this->createMock(ObjectType::class),
                $this->createMock(CollectionType::class),
            )],
        ];
    }

    /**
     * @dataProvider reflectionFactoryDataProvider
     */
    public function testItCreatesTypeThatRequiresReflectionFactory(TypeInterface $type): void
    {
        $this->reflectionTypeFactory->expects($this->once())
            ->method('create')
            ->willReturn($type);

        $this->docBlockTypeFactory->expects($this->never())
            ->method('create');

        self::assertSame($type, $this->parameterTypeFactory->create($this->createMock(\ReflectionParameter::class)));
    }

    /**
     * @return array<array{TypeInterface&MockObject}|array{TypeInterface}>
     */
    public function reflectionFactoryDataProvider(): array
    {
        return [
            [$this->createMock(IntegerType::class)],
            [$this->createMock(StringType::class)],
            [$this->createMock(FloatType::class)],
            [$this->createMock(BooleanType::class)],
            [$this->createMock(NullType::class)],
            [$this->createMock(MixedType::class)],
            [$this->createMock(ObjectType::class)],
            [new NullableType($this->createMock(StringType::class))],
            [new CompoundType(
                $this->createMock(ObjectType::class),
                $this->createMock(StringType::class),
            )],
        ];
    }
}
