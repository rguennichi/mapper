<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\ScalarType;
use PHPUnit\Framework\TestCase;

class BooleanTypeTest extends TestCase
{
    public function testInstanceOfScalarType(): void
    {
        $type = new BooleanType();

        self::assertInstanceOf(ScalarType::class, $type);
    }

    public function testStringValue(): void
    {
        $type = new BooleanType();

        self::assertSame('bool', $type->__toString());
    }
}
