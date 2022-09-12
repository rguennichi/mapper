<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\ScalarType;
use PHPUnit\Framework\TestCase;

class FloatTypeTest extends TestCase
{
    public function testInstanceOfScalarType(): void
    {
        $type = new FloatType();

        self::assertInstanceOf(ScalarType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new FloatType();

        self::assertSame('float', $type->__toString());
    }
}
