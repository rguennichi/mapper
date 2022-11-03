<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\AggregatedType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;

class CompoundTypeTest extends TestCase
{
    public function testInstanceOfAggregatedType(): void
    {
        $type = new CompoundType(new StringType(), new IntegerType());

        self::assertInstanceOf(AggregatedType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new CompoundType(new NullType(), new StringType(), new BooleanType(), new ObjectType(\stdClass::class));

        self::assertSame('null|string|bool|stdClass', $type->__toString());
    }
}
