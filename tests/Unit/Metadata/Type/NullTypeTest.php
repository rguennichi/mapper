<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\NullType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class NullTypeTest extends TestCase
{
    /**
     * @param class-string<\Throwable>|null $expectedException
     */
    #[TestWith([null, null])]
    #[TestWith([false, InvalidTypeException::class])]
    #[TestWith([true, InvalidTypeException::class])]
    #[TestWith(['', InvalidTypeException::class])]
    #[TestWith([0, InvalidTypeException::class])]
    public function testResolve(mixed $value, ?string $expectedException): void
    {
        if ($expectedException) {
            self::expectException($expectedException);
        }

        self::assertNull((new NullType())->resolve($value, self::createMock(Argument::class)));
    }
}
