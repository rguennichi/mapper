<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\TrueType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class TrueTypeTest extends TestCase
{
    /**
     * @param class-string<\Throwable>|null $expectedException
     */
    #[TestWith([true, null])]
    #[TestWith([false, InvalidTypeException::class])]
    #[TestWith([null, InvalidTypeException::class])]
    #[TestWith(['', InvalidTypeException::class])]
    #[TestWith([0, InvalidTypeException::class])]
    public function testResolve(mixed $value, ?string $expectedException): void
    {
        if ($expectedException) {
            self::expectException($expectedException);
        }

        self::assertTrue((new TrueType())->resolve($value, self::createMock(Argument::class)));
    }
}
