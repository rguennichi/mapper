<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use Benchmark\Guennichi\Mapper\Fixture\Head;
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\MapperInterface;
use PhpBench\Attributes\BeforeMethods;

#[BeforeMethods('setUp')]
class GuennichiMapperBench
{
    private MapperInterface $mapper;

    public function setUp(): void
    {
        $this->mapper = new Mapper();

        $this->mapper->map(Head::INPUT, Head::class);
    }

    public function benchGuennichiMap(): void
    {
        $this->mapper->map(Head::INPUT, Head::class);
    }
}
