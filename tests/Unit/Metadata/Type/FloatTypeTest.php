<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class FloatTypeTest extends TestCase
{
    /**
     * @param class-string<\Throwable>|float $expectedResult
     */
    #[TestWith([12.656, false, 12.656])]
    #[TestWith([12, false, 12.])]
    #[TestWith(['12.542', true, 12.542])]
    #[TestWith([true, true, 1.])]
    #[TestWith([false, true, 0.])]
    #[TestWith(['12.542', false, InvalidTypeException::class])]
    #[TestWith([true, false, InvalidTypeException::class])]
    #[TestWith([false, false, InvalidTypeException::class])]
    public function testResolve(mixed $value, bool $flexible, float|string $expectedResult): void
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

        if (\is_string($expectedResult)) {
            self::expectException($expectedResult);
        }

        self::assertSame($expectedResult, (new FloatType())->resolve($value, $argument));
    }
}
