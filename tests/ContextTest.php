<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testVisits(): void
    {
        $createParameter = fn (string $name) => new Parameter(
            $name,
            $this->createMock(TypeInterface::class),
            true,
            [],
        );

        $context = new Context(\stdClass::class);

        self::assertSame(\stdClass::class, $context->classname());

        $context->visitParameter($createParameter('param1'));

        self::assertEquals($createParameter('param1'), $context->parameter());

        self::assertEquals([
            \stdClass::class => [
                'param1' => $createParameter('param1'),
            ],
        ], $context->visits());

        $context->visitParameter($createParameter('param2'));

        self::assertEquals($createParameter('param2'), $context->parameter());

        self::assertEquals([
            \stdClass::class => [
                'param1' => $createParameter('param1'),
                'param2' => $createParameter('param2'),
            ],
        ], $context->visits());

        $context->visitClassname(\Directory::class);

        self::assertSame(\Directory::class, $context->classname());

        self::assertEquals($createParameter('param2'), $context->parameter());

        self::assertEquals([
            \stdClass::class => [
                'param1' => $createParameter('param1'),
                'param2' => $createParameter('param2'),
            ],
            \Directory::class => [],
        ], $context->visits());
    }
}
