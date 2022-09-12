<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Factory\Type\Source;

use ArrayIterator;
use Guennichi\Mapper\Metadata\Factory\Type\Source\PhpDocumentorParameterTypeFactory;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Intersection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionParameter;
use stdClass;

class PhpDocumentorParameterTypeFactoryTest extends TestCase
{
    private PhpDocumentorParameterTypeFactory $typeFactory;

    protected function setUp(): void
    {
        $this->typeFactory = new PhpDocumentorParameterTypeFactory(
            DocBlockFactory::createInstance(),
            new ContextFactory(),
        );
    }

    /** @dataProvider validPhpDocTypesDataProvider */
    public function testItCreatesInternalTypeFromReflection(ReflectionParameter $reflectionParameter, TypeInterface $expectedType): void
    {
        self::assertEquals($expectedType, $this->typeFactory->create($reflectionParameter));
    }

    /**
     * @return array<array-key, array{ReflectionParameter|null, TypeInterface}>
     */
    public function validPhpDocTypesDataProvider(): array
    {
        return [
            'string_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param string $param
                     */
                    public function __construct(public $param = 'value')
                    {
                    }
                }),
                new StringType(),
            ],
            'integer_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param int $param
                     */
                    public function __construct(public $param = 0)
                    {
                    }
                }),
                new IntegerType(),
            ],
            'float_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param float $param
                     */
                    public function __construct(public $param = 0.)
                    {
                    }
                }),
                new FloatType(),
            ],
            'boolean_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param bool $param
                     */
                    public function __construct(public $param = true)
                    {
                    }
                }),
                new BooleanType(),
            ],
            'array_type' => [
                $this->createReflectionParameter(new class() {
                    /** @param array<string, string> $param */
                    public function __construct(public $param = [])
                    {
                    }
                }),
                new ArrayType(new StringType(), new StringType()),
            ],
            'collection_type' => [
                $this->createReflectionParameter(new class() {
                    /** @param ArrayIterator<int, stdClass> $param */
                    public function __construct(public $param = new ArrayIterator([]))
                    {
                    }
                }),
                new CollectionType(ArrayIterator::class, new ObjectType(stdClass::class)),
            ],
            'object_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param stdClass $param
                     */
                    public function __construct(public $param = new stdClass())
                    {
                    }
                }),
                new ObjectType(stdClass::class),
            ],
            'nullable_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param ?stdClass $param
                     */
                    public function __construct(public $param = null)
                    {
                    }
                }),
                new NullableType(new ObjectType(stdClass::class)),
            ],
            'compound_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param string|int|null $param
                     */
                    public function __construct(public $param = null)
                    {
                    }
                }),
                new CompoundType(new StringType(), new IntegerType(), new NullType()),
            ],
        ];
    }

    /** @dataProvider invalidPhpDocTypesDataProvider */
    public function testItThrowsAnExceptionWhenInvalidTypeIsProvided(ReflectionParameter $reflectionParameter, string $expectedExceptionMessage): void
    {
        self::expectExceptionMessage($expectedExceptionMessage);

        $this->typeFactory->create($reflectionParameter);
    }

    /**
     * @return array<array-key, array{ReflectionParameter|null, string}>
     */
    public function invalidPhpDocTypesDataProvider(): array
    {
        return [
            'intersection_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @param stdClass&MockObject $param
                     */
                    public function __construct(public $param = null)
                    {
                    }
                }),
                sprintf('Type "%s" is not supported', Intersection::class),
            ],
            'array_key_float_type' => [
                $this->createReflectionParameter(new class() {
                    /**
                     * @phpstan-ignore-next-line
                     *
                     * @param array<float, string> $param
                     */
                    public function __construct(public $param = null)
                    {
                    }
                }),
                'An array can have only integers or strings as keys',
            ],
        ];
    }

    private function createReflectionParameter(object $object): ?ReflectionParameter
    {
        return (new ReflectionClass($object))->getConstructor()?->getParameters()[0];
    }
}
