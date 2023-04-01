<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Mapper\Attribute\Trusted;

#[Trusted]
class Image
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
    ) {
    }
}
