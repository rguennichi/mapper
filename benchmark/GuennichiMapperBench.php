<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use Benchmark\Guennichi\Mapper\Fixture\Head;
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\MapperInterface;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\ParameterFactory;
use Guennichi\Mapper\Metadata\Factory\Type\ParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\PhpDocumentorParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\ReflectionParameterTypeFactory;
use Guennichi\Mapper\Metadata\Repository\ConstructorInMemoryRepository;
use PhpBench\Attributes\BeforeMethods;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;

#[BeforeMethods('setUp')]
class GuennichiMapperBench
{
    private MapperInterface $mapper;

    public function setUp(): void
    {
        $this->mapper = new Mapper(
            new ConstructorFetcher(
                new ConstructorFactory(
                    new ParameterFactory(
                        new ParameterTypeFactory(
                            new ReflectionParameterTypeFactory(),
                            new PhpDocumentorParameterTypeFactory(
                                DocBlockFactory::createInstance(),
                                new ContextFactory(),
                            ),
                        ),
                    ),
                ),
                new ConstructorInMemoryRepository(),
            ),
        );

        $this->mapper->map(Head::INPUT, Head::class);
    }

    public function benchGuennichiMap(): void
    {
        $this->mapper->map(Head::INPUT, Head::class);
    }
}
