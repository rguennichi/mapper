<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\MixedType;
use PHPUnit\Framework\TestCase;

class MixedTypeTest extends TestCase
{
    public function testStringValue(): void
    {
        $type = new MixedType();

        self::assertSame('mixed', $type->__toString());
    }
}
