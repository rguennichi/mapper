<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture\Tmp;

class Child
{
    public function __construct(
        public readonly string $paramOne,
        public readonly ?float $paramTwo,
    ) {
    }
}
