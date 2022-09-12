<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;

class NullableTypeTest extends TestCase
{
    public function testStringValue(): void
    {
        $type = new NullableType(new StringType());

        self::assertSame('?string', $type->__toString());
    }
}
