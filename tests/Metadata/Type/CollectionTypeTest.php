<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use ArrayIterator;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\ListType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;
use stdClass;

class CollectionTypeTest extends TestCase
{
    public function testInstanceOfListType(): void
    {
        $type = new CollectionType(ArrayIterator::class, new StringType());

        self::assertInstanceOf(ListType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new CollectionType(ArrayIterator::class, new ObjectType(stdClass::class));

        self::assertSame('ArrayIterator<stdClass>', $type->__toString());
    }
}
