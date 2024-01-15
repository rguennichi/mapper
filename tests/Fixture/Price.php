<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Mapper\Attribute\Trusted;

#[Trusted]
class Price
{
    public function __construct(public readonly ?int $cents = null)
    {
    }
}
