<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Type\DateTimeType;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{
    public function testDateTimeTypeStringValue(): void
    {
        $type = new DateTimeType(\DateTimeInterface::class);

        self::assertSame(\DateTimeInterface::class, $type->__toString());
    }

    public function testItFailsIfClassnameIsNotDateTime(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expecting "DateTimeInterface|DateTimeImmutable|DateTime", "stdClass" given');

        new DateTimeType(\stdClass::class);
    }
}
