<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{
    /**
     * @param class-string<\DateTimeInterface> $type
     */
    #[TestWith(['2021-09-26', \DateTime::class, '2021-09-26'])]
    #[TestWith(['2021-09-26', \DateTimeImmutable::class, '2021-09-26'])]
    #[TestWith(['2021-09-26', \DateTimeInterface::class, '2021-09-26'])]
    #[TestWith(['2021-09-26', \DateTimeInterface::class, ['date' => '2021-09-26 00:00:00.000000', 'timezone_type' => 3, 'timezone' => 'UTC']])]
    public function testItResolvesNativeDateTimeTypes(string $expectedResult, string $type, mixed $value): void
    {
        $dateTime = (new DateTimeType($type))->resolve($value, $this->createMock(Argument::class));

        self::assertInstanceOf($type, $dateTime);
        self::assertSame($expectedResult, $dateTime->format('Y-m-d'));
    }

    public function testItResolvesCustomDateTimeTypes(): void
    {
        $customDateTime = new class() extends \DateTimeImmutable {};

        $dateTime = (new DateTimeType($customDateTime::class))->resolve('2021-09-26', $this->createMock(Argument::class));

        self::assertInstanceOf($customDateTime::class, $dateTime);
        self::assertSame('2021-09-26', $dateTime->format('Y-m-d'));
    }

    public function testItResolvesDateTimeWithCustomFormat(): void
    {
        $argument = new Argument(
            'Property',
            self::createMock(TypeInterface::class),
            false,
            false,
            false,
            false,
            [DateTimeFormat::class => new DateTimeFormat('m Y H:i')],
        );

        $dateTime = (new DateTimeType(\DateTimeInterface::class))->resolve('09 2021 21:40', $argument);

        self::assertInstanceOf(\DateTimeImmutable::class, $dateTime);
        self::assertSame('2021-09 21-40', $dateTime->format('Y-m H-i'));
    }
}
