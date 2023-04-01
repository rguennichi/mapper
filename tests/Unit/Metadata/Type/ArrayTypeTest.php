<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class ArrayTypeTest extends TestCase
{
    public function testResolve(): void
    {
        self::expectException(ResolverNotFoundException::class);

        (
        new ArrayType(
            self::createMock(TypeInterface::class),
            self::createMock(TypeInterface::class),
        )
        )->resolve(self::any(), self::createMock(Argument::class));
    }
}
