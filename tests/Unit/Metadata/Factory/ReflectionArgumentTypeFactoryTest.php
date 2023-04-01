<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Factory;

use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class ReflectionArgumentTypeFactoryTest extends TestCase
{
    private ReflectionArgumentTypeFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ReflectionArgumentTypeFactory();
    }

    /**
     * @dataProvider factoryDataProvider
     */
    public function testItCreatesTypeFromReflectionParameter(\ReflectionParameter $reflectionParameter, TypeInterface $expectedType): void
    {
        self::assertEquals($expectedType, $this->factory->__invoke($reflectionParameter));
    }

    /**
     * @return \Generator<array{\ReflectionParameter, TypeInterface}>
     */
    public static function factoryDataProvider(): \Generator
    {
        $parameters = (new \ReflectionMethod(new class() {
            public function __construct(
                public readonly string $arg1 = '',
                public readonly int $arg2 = 0,
                public readonly float $arg3 = 1.2,
                public readonly bool $arg4 = true,
                public readonly array $arg5 = [],
                public readonly \stdClass $arg6 = new \stdClass(),
                public readonly \ArrayObject $arg7 = new \ArrayObject(),
                public readonly mixed $arg8 = '',
                public readonly \DateTimeInterface $arg9 = new \DateTime(),
                public readonly \DateTimeImmutable $arg10 = new \DateTimeImmutable(),
                public readonly string|bool $arg11 = '',
                public readonly ?\stdClass $arg12 = null,
            ) {
            }
        }, '__construct'))->getParameters();

        $parameter = static fn (int $index) => $parameters[$index - 1];

        yield [
            $parameter(1),
            new StringType(),
        ];

        yield [
            $parameter(2),
            new IntegerType(),
        ];

        yield [
            $parameter(3),
            new FloatType(),
        ];

        yield [
            $parameter(4),
            new BooleanType(),
        ];

        yield [
            $parameter(5),
            new ArrayType(new CompoundType([new StringType(), new IntegerType()]), new MixedType()),
        ];

        yield [
            $parameter(6),
            new ObjectType(\stdClass::class),
        ];

        yield [
            $parameter(7),
            new ObjectType(\ArrayObject::class),
        ];

        yield [
            $parameter(8),
            new MixedType(),
        ];

        yield [
            $parameter(9),
            new DateTimeType(\DateTimeImmutable::class),
        ];

        yield [
            $parameter(10),
            new DateTimeType(\DateTimeImmutable::class),
        ];

        yield [
            $parameter(11),
            new CompoundType([new StringType(), new BooleanType()]),
        ];

        yield [
            $parameter(12),
            new NullableType(new ObjectType(\stdClass::class)),
        ];
    }
}
