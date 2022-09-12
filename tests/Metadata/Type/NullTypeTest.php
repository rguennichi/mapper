<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\NullType;
use PHPUnit\Framework\TestCase;

class NullTypeTest extends TestCase
{
    public function testStringValue(): void
    {
        $type = new NullType();

        self::assertSame('null', $type->__toString());
    }
}
