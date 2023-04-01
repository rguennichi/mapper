<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use PHPUnit\Framework\TestCase;

class ObjectTypeTest extends TestCase
{
    public function testResolve(): void
    {
        self::expectException(ResolverNotFoundException::class);

        (new ObjectType(\stdClass::class))
            ->resolve(self::any(), self::createMock(Argument::class));
    }
}
