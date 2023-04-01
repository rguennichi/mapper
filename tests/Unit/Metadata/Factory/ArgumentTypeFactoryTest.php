<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Factory;

use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactoryInterface;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\FalseType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TrueType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class ArgumentTypeFactoryTest extends TestCase
{
    /**
     * @dataProvider factoryDataProvider
     */
    public function testItCreatesArgumentType(TypeInterface $typeByReflection, bool $requiresDocComment): void
    {
        $docCommentTypeFactory = $this->createMock(ArgumentTypeFactoryInterface::class);
        $reflectionTypeFactory = $this->createMock(ArgumentTypeFactoryInterface::class);

        $reflectionTypeFactory->expects($this->once())
            ->method('__invoke')
            ->willReturn($typeByReflection);

        $docCommentTypeFactory->expects($requiresDocComment ? $this->once() : $this->never())
            ->method('__invoke');

        (new ArgumentTypeFactory($docCommentTypeFactory, $reflectionTypeFactory))->__invoke(
            $this->createMock(\ReflectionParameter::class),
        );
    }

    public static function factoryDataProvider(): \Generator
    {
        // Requires docComment type extraction
        yield [
            new ArrayType(new IntegerType(), new MixedType()),
            true,
        ];

        yield [
            new CollectionType(\ArrayObject::class, new ArrayType(new IntegerType(), new MixedType())),
            true,
        ];

        yield [
            new MixedType(),
            true,
        ];

        yield [
            new CompoundType([new IntegerType(), new ArrayType(new IntegerType(), new MixedType())]),
            true,
        ];

        yield [
            new CompoundType([new IntegerType(), new MixedType()]),
            true,
        ];
        // Does not require doc comment type extraction
        yield [
            new IntegerType(),
            false,
        ];

        yield [
            new FloatType(),
            false,
        ];

        yield [
            new BooleanType(),
            false,
        ];

        yield [
            new StringType(),
            false,
        ];

        yield [
            new ObjectType(\stdClass::class),
            false,
        ];

        yield [
            new TrueType(),
            false,
        ];

        yield [
            new FalseType(),
            false,
        ];

        yield [
            new NullType(),
            false,
        ];

        yield [
            new CompoundType([new StringType(), new FloatType(), new BooleanType()]),
            false,
        ];
    }
}
