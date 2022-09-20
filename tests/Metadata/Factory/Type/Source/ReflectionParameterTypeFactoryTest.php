<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Factory\Type\Source;

use ArrayIterator;
use Countable;
use Guennichi\Mapper\Metadata\Factory\Type\Source\ReflectionParameterTypeFactory;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionParameter;
use stdClass;

class ReflectionParameterTypeFactoryTest extends TestCase
{
    private ReflectionParameterTypeFactory $typeFactory;

    protected function setUp(): void
    {
        $this->typeFactory = new ReflectionParameterTypeFactory();
    }

    /** @dataProvider validReflectionTypesDataProvider */
    public function testItCreatesInternalTypeFromReflection(ReflectionParameter $reflectionParameter, TypeInterface $expectedType): void
    {
        self::assertEquals($expectedType, $this->typeFactory->create($reflectionParameter));
    }

    /**
     * @return array<array-key, array{ReflectionParameter|null, TypeInterface}>
     */
    public function validReflectionTypesDataProvider(): array
    {
        return [
            'string_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly string $param = 'value')
                    {
                    }
                }),
                new StringType(),
            ],
            'integer_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly int $param = 0)
                    {
                    }
                }),
                new IntegerType(),
            ],
            'float_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly float $param = 0.)
                    {
                    }
                }),
                new FloatType(),
            ],
            'boolean_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly bool $param = true)
                    {
                    }
                }),
                new BooleanType(),
            ],
            'array_type' => [
                $this->createReflectionParameter(new class() {
                    /** @param array<string> $param */
                    public function __construct(public readonly array $param = [])
                    {
                    }
                }),
                new ArrayType(new IntegerType(), new MixedType()),
            ],
            'collection_type' => [
                $this->createReflectionParameter(new class() {
                    /** @param ArrayIterator<int, string> $param */
                    public function __construct(public readonly ArrayIterator $param = new ArrayIterator([]))
                    {
                    }
                }),
                new CollectionType(ArrayIterator::class, new MixedType()),
            ],
            'object_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly stdClass $param = new stdClass())
                    {
                    }
                }),
                new ObjectType(stdClass::class),
            ],
            'datetime_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly \DateTimeInterface $param = new \DateTimeImmutable())
                    {
                    }
                }),
                new DateTimeType(\DateTimeInterface::class),
            ],
            'nullable_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly ?stdClass $param = null)
                    {
                    }
                }),
                new NullableType(new ObjectType(stdClass::class)),
            ],
            'compound_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly string|int|null $param = null)
                    {
                    }
                }),
                new CompoundType(new StringType(), new IntegerType(), new NullType()),
            ],
        ];
    }

    /** @dataProvider invalidReflectionTypesDataProvider */
    public function testItThrowsAnExceptionWhenInvalidTypeIsProvided(ReflectionParameter $reflectionParameter, string $expectedExceptionMessage): void
    {
        self::expectExceptionMessage($expectedExceptionMessage);

        $this->typeFactory->create($reflectionParameter);
    }

    /**
     * @return array<array-key, array{ReflectionParameter|null, string}>
     */
    public function invalidReflectionTypesDataProvider(): array
    {
        return [
            'intersection_type' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(public readonly Iterator&Countable $param = new ArrayIterator())
                    {
                    }
                }),
                sprintf('Type "%s" is not supported', ReflectionIntersectionType::class),
            ],
        ];
    }

    private function createReflectionParameter(object $object): ?ReflectionParameter
    {
        return (new ReflectionClass($object))->getConstructor()?->getParameters()[0];
    }
}
