<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BooleanTypeTest extends TestCase
{
    private BooleanType $type;

    protected function setUp(): void
    {
        $this->type = new BooleanType();
    }

    /**
     * @param bool|class-string<\Throwable> $expectedResult
     */
    #[DataProvider('resolveDataProvider')]
    public function testResolve(mixed $value, bool $flexible, bool|string $expectedResult): void
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

        self::assertSame($expectedResult, $this->type->resolve($value, $argument));
    }

    /**
     * @return \Generator<array{mixed, bool, bool|class-string}>
     */
    public static function resolveDataProvider(): \Generator
    {
        yield [true, false, true];
        yield [true, true, true];
        yield [false, false, false];
        yield [false, true, false];
        yield ['true', true, true];
        yield ['false', true, false];
        yield ['1', true, true];
        yield ['0', true, false];
        yield ['on', true, true];
        yield ['off', true, false];
        yield ['yes', true, true];
        yield ['no', true, false];
        yield [1, true, true];
        yield [0, true, false];
        yield [null, true, false];
        yield ['true', false, InvalidTypeException::class];
        yield ['false', false, InvalidTypeException::class];
        yield ['1', false, InvalidTypeException::class];
        yield ['0', false, InvalidTypeException::class];
        yield ['on', false, InvalidTypeException::class];
        yield ['off', false, InvalidTypeException::class];
        yield ['yes', false, InvalidTypeException::class];
        yield ['no', false, InvalidTypeException::class];
        yield [1, false, InvalidTypeException::class];
        yield [0, false, InvalidTypeException::class];
        yield [null, false, InvalidTypeException::class];
    }
}
