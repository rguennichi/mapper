<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\ObjectType;
use PHPUnit\Framework\TestCase;

class ObjectTypeTest extends TestCase
{
    public function testObjectTypeStringValue(): void
    {
        $type = new ObjectType(\stdClass::class);

        self::assertSame(\stdClass::class, $type->__toString());
    }
}
