<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper\Fixture;

class Child
{
    public function __construct(
        public readonly string $paramOne,
        public readonly ?float $paramTwo,
    ) {
    }
}
