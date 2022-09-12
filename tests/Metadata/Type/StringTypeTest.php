<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\ScalarType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;

class StringTypeTest extends TestCase
{
    public function testInstanceOfScalarType(): void
    {
        $type = new StringType();

        self::assertInstanceOf(ScalarType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new StringType();

        self::assertSame('string', $type->__toString());
    }
}
