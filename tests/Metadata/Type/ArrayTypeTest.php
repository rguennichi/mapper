<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\ListType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArrayTypeTest extends TestCase
{
    public function testInstanceOfListType(): void
    {
        $type = new ArrayType(new IntegerType(), new StringType());

        self::assertInstanceOf(ListType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new ArrayType(
            new IntegerType(),
            new ArrayType(
                new StringType(),
                new ObjectType(stdClass::class),
            ),
        );

        self::assertSame('array<int, array<string, stdClass>>', $type->__toString());
    }
}
