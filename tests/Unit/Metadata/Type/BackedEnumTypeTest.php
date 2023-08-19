<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\BackedEnumType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\IntegerEnum;
use Tests\Guennichi\Mapper\Fixture\StringEnum;

class BackedEnumTypeTest extends TestCase
{
    #[TestWith([1, IntegerEnum::class, new IntegerType(), IntegerEnum::CASE_1])]
    #[TestWith([2, IntegerEnum::class, new IntegerType(), IntegerEnum::CASE_2])]
    #[TestWith(['case_1', StringEnum::class, new StringType(), StringEnum::CASE_1])]
    #[TestWith(['case_2', StringEnum::class, new StringType(), StringEnum::CASE_2])]
    public function testResolve(string|int $value, string $enumClassname, StringType|IntegerType $backingType, \BackedEnum $expectedResult): void
    {
        $argument = new Argument(
            'Property',
            self::createMock(TypeInterface::class),
            false,
            false,
            false,
            false,
            [],
        );

        self::assertTrue(is_subclass_of($enumClassname, \BackedEnum::class));

        self::assertSame($expectedResult, (new BackedEnumType($enumClassname, $backingType))->resolve($value, $argument));
    }
}
