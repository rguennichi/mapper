<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{
    public function testItResolvesCustomDateTimeTypes(): void
    {
        $class = new class() extends \DateTimeImmutable {};
        $customDateTime = (new DateTimeType($class::class))->resolve('2021-09-26', $this->createMock(Context::class));

        self::assertInstanceOf($class::class, $customDateTime);
        self::assertSame('2021-09-26', $customDateTime->format('Y-m-d'));
    }

    public function testItResolvesNativeDateTimeTypes(): void
    {
        $mutableDateTime = (new DateTimeType(\DateTime::class))->resolve('2021-09-26', $this->createMock(Context::class));
        $immutableDateTime = (new DateTimeType(\DateTimeImmutable::class))->resolve('2021-09-26', $this->createMock(Context::class));
        $interfaceDateTime = (new DateTimeType(\DateTimeInterface::class))->resolve('2021-09-26', $this->createMock(Context::class));

        self::assertInstanceOf(\DateTime::class, $mutableDateTime);
        self::assertInstanceOf(\DateTimeImmutable::class, $immutableDateTime);
        self::assertInstanceOf(\DateTimeImmutable::class, $interfaceDateTime);

        self::assertSame('2021-09-26', $mutableDateTime->format('Y-m-d'));
        self::assertSame('2021-09-26', $immutableDateTime->format('Y-m-d'));
        self::assertSame('2021-09-26', $interfaceDateTime->format('Y-m-d'));
    }

    public function testDateTimeTypeStringValue(): void
    {
        $type = new DateTimeType(\DateTimeInterface::class);

        self::assertSame(\DateTimeInterface::class, $type->__toString());
    }

    public function testItFailsIfClassnameIsNotDateTime(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expecting instance of "DateTimeInterface", "stdClass" given');

        /* @phpstan-ignore-next-line */
        new DateTimeType(\stdClass::class);
    }
}
