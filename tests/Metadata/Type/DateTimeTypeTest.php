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

    /**
     * @testWith ["2021-09-26", "DateTime", "2021-09-26"]
     *           ["2021-09-26", "DateTimeImmutable", "2021-09-26"]
     *           ["2021-09-26", "DateTimeInterface", "2021-09-26"]
     *           ["2021-09-26", "DateTimeInterface", {"date":"2021-09-26 00:00:00.000000","timezone_type":3,"timezone":"UTC"}]
     *
     * @param class-string<\DateTimeInterface> $type
     */
    public function testItResolvesNativeDateTimeTypes(string $expected, string $type, mixed $input): void
    {
        $dateTime = (new DateTimeType($type))->resolve($input, $this->createMock(Context::class));

        self::assertInstanceOf($type, $dateTime);
        self::assertSame($expected, $dateTime->format('Y-m-d'));
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
