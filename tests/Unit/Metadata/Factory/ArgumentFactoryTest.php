<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Factory;

use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\CustomAttribute;

class ArgumentFactoryTest extends TestCase
{
    public function testItCreatesArgumentObject(): void
    {
        $factory = new ArgumentFactory(
            $typeFactory = $this->createMock(ArgumentTypeFactory::class),
        );

        $typeFactory->expects($this->once())
            ->method('__invoke')
            ->willReturn(new StringType());

        self::assertEquals(
            new Argument(
                'example',
                new StringType(),
                true,
                false,
                false,
                true,
                [
                    CustomAttribute::class => new CustomAttribute(),
                    Flexible::class => new Flexible(),
                ],
            ),
            $factory->__invoke(new \ReflectionParameter([
                    new class() {
                        public function __construct(
                            #[CustomAttribute]
                            #[Flexible]
                            public readonly string $example = '',
                        ) {
                        }
                    },
                    '__construct',
                ], 'example'),
            ),
        );
    }
}
