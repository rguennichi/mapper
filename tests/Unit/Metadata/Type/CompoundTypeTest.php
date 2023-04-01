<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Type;

use Guennichi\Mapper\Exception\ResolverNotFoundException;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class CompoundTypeTest extends TestCase
{
    public function testResolve(): void
    {
        self::expectException(ResolverNotFoundException::class);

        (new CompoundType([
            $this->createMock(TypeInterface::class),
            $this->createMock(TypeInterface::class),
        ]))->resolve(self::any(), self::createMock(Argument::class));
    }
}
