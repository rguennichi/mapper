<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\ScalarType;
use PHPUnit\Framework\TestCase;

class IntegerTypeTest extends TestCase
{
    public function testInstanceOfScalarType(): void
    {
        $type = new IntegerType();

        self::assertInstanceOf(ScalarType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new IntegerType();

        self::assertSame('int', $type->__toString());
    }
}
