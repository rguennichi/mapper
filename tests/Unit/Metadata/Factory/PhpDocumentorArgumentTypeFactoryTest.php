<?php

/** @noinspection PhpMissingParamTypeInspection */

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Factory;

use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
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
use Tests\Guennichi\Mapper\Fixture\Collection;

class PhpDocumentorArgumentTypeFactoryTest extends TestCase
{
    private PhpDocumentorArgumentTypeFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new PhpDocumentorArgumentTypeFactory();
    }

    /**
     * @dataProvider validTypesDataProvider
     */
    public function testItCreatesTypeFromReflectionParameter(\ReflectionParameter $reflectionParameter, TypeInterface $expectedType): void
    {
        self::assertEquals($expectedType, $this->factory->__invoke($reflectionParameter));
    }

    public function testItThrowsAnExceptionIfDocTypeOfArgumentNotFound(): void
    {
        self::expectExceptionMessageMatches('#Doc type not found for "\$example1" in "class@anonymous#');

        $this->factory->__invoke(new \ReflectionParameter([
            new class() {
                /**
                 * @param string $example2
                 */
                public function __construct(
                    public $example1 = '',
                    public $example2 = '',
                ) {
                }
            },
            '__construct',
        ], 'example1'));
    }

    public function testItThrowsAnExceptionIfDocCommentNotFound(): void
    {
        self::expectExceptionMessageMatches('#Doc type not found for "\$example1" in "class@anonymous#');

        $this->factory->__invoke(new \ReflectionParameter([
            new class() {
                public function __construct(
                    public $example1 = '',
                    public $example2 = '',
                ) {
                }
            },
            '__construct',
        ], 'example1'));
    }

    /**
     * @return \Generator<array{\ReflectionParameter, TypeInterface}>
     */
    public static function validTypesDataProvider(): \Generator
    {
        $parameters = (new \ReflectionMethod(new class() {
            /**
             * @param string $arg1
             * @param int $arg2
             * @param float $arg3
             * @param bool $arg4
             * @param array $arg5
             * @param \stdClass $arg6
             * @param \ArrayObject $arg7
             * @param mixed $arg8
             * @param \DateTimeInterface $arg9
             * @param \DateTimeImmutable $arg10
             * @param string|float $arg11
             * @param \stdClass|null $arg12
             * @param \Tests\Guennichi\Mapper\Fixture\Collection<\stdClass> $arg13
             */
            public function __construct(
                public $arg1 = '',
                public $arg2 = 0,
                public $arg3 = 1.2,
                public $arg4 = true,
                public $arg5 = [],
                public $arg6 = new \stdClass(),
                public $arg7 = new \ArrayObject(),
                public $arg8 = '',
                public $arg9 = new \DateTime(),
                public $arg10 = new \DateTimeImmutable(),
                public $arg11 = '',
                public $arg12 = null,
                public $arg13 = null,
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
            new CompoundType([new StringType(), new FloatType()]),
        ];

        yield [
            $parameter(12),
            new NullableType(new ObjectType(\stdClass::class)),
        ];

        yield [
            $parameter(13),
            new CollectionType(Collection::class, new ObjectType(\stdClass::class)),
        ];
    }
}
