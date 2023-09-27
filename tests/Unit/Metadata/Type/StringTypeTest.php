<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class StringTypeTest extends TestCase
{
    /**
     * @param class-string<\Throwable> $expectedResult
     */
    #[TestWith(['example', false, 'example'])]
    #[TestWith([null, true, ''])]
    #[TestWith([12, true, '12'])]
    #[TestWith([12.542, true, '12.542'])]
    #[TestWith([true, true, '1'])]
    #[TestWith([false, true, ''])]
    #[TestWith([null, false, InvalidTypeException::class])]
    #[TestWith([12, false, InvalidTypeException::class])]
    #[TestWith([12.542, false, InvalidTypeException::class])]
    #[TestWith([true, false, InvalidTypeException::class])]
    #[TestWith([false, false, InvalidTypeException::class])]
    public function testResolve(mixed $value, bool $flexible, string $expectedResult): void
    {
        $argument = new Argument(
            'Property',
            self::createMock(TypeInterface::class),
            false,
            false,
            false,
            $flexible,
            [],
        );

        if (class_exists($expectedResult)) {
            self::expectException($expectedResult);
        }

        self::assertSame($expectedResult, (new StringType())->resolve($value, $argument));
    }
}
