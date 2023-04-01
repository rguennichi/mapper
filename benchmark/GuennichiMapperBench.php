<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\MapperInterface;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Repository\PhpCacheFileConstructorRepository;
use PhpBench\Attributes\BeforeMethods;
use Tests\Guennichi\Mapper\Fixture\Product;

class GuennichiMapperBench
{
    private MapperInterface $mapper;

    public function setUp(): void
    {
        $cacheRepository = new PhpCacheFileConstructorRepository(__DIR__ . '/../var/cache');
        $cacheRepository->clear();

        $this->mapper = new Mapper(
            new ConstructorFetcher(
                new ConstructorFactory(
                    new ArgumentFactory(
                        new ArgumentTypeFactory(
                            new PhpDocumentorArgumentTypeFactory(),
                            new ReflectionArgumentTypeFactory(),
                        ),
                    ),
                ),
                $cacheRepository,
            ),
        );
        $this->mapper->__invoke(Product::getMock(), Product::class);
    }

    #[BeforeMethods('setUp')]
    public function benchGuennichiMap(): void
    {
        $this->mapper->__invoke(Product::getMock(), Product::class);
    }
}
