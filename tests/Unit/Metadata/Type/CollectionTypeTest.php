<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class CollectionTypeTest extends TestCase
{
    public function testResolve(): void
    {
        self::expectException(ResolverNotFoundException::class);

        (
        new CollectionType(
            \stdClass::class,
            self::createMock(TypeInterface::class),
        )
        )->resolve(self::any(), self::createMock(Argument::class));
    }
}
