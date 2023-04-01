<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class IntegerTypeTest extends TestCase
{
    /**
     * @param class-string<\Throwable>|int $expectedResult
     */
    #[TestWith([12, false, 12])]
    #[TestWith(['150', true, 150])]
    #[TestWith([3.14, true, 3])]
    #[TestWith(['150', false, InvalidTypeException::class])]
    #[TestWith([3.14, false, InvalidTypeException::class])]
    public function testResolve(mixed $value, bool $flexible, int|string $expectedResult): void
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

        self::assertSame($expectedResult, (new IntegerType())->resolve($value, $argument));
    }
}
