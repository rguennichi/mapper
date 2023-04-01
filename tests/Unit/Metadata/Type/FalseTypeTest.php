<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\FalseType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class FalseTypeTest extends TestCase
{
    /**
     * @param class-string<\Throwable>|null $expectedException
     */
    #[TestWith([false, null])]
    #[TestWith([true, InvalidTypeException::class])]
    #[TestWith([null, InvalidTypeException::class])]
    #[TestWith(['', InvalidTypeException::class])]
    #[TestWith([0, InvalidTypeException::class])]
    public function testResolve(mixed $value, ?string $expectedException): void
    {
        if ($expectedException) {
            self::expectException($expectedException);
        }

        self::assertFalse((new FalseType())->resolve($value, self::createMock(Argument::class)));
    }
}
