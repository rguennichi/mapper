<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use Benchmark\Guennichi\Mapper\Fixture\Head;
use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use PhpBench\Attributes\BeforeMethods;

#[BeforeMethods('setUp')]
class ValinorMapperBench
{
    private TreeMapper $mapper;

    public function setUp(): void
    {
        $this->mapper = (new MapperBuilder())
            ->withCache(new FileSystemCache(__DIR__ . '/../cache'))
            ->flexible()
            ->mapper();

        $this->mapper->map(Head::class, Head::INPUT);
    }

    public function benchValinorMap(): void
    {
        $this->mapper->map(Head::class, Head::INPUT);
    }
}
