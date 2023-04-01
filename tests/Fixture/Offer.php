<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Mapper\Attribute\Trusted;

#[Trusted]
class Offer
{
    public function __construct(
        public readonly string $title,
        public readonly Image $image,
        public readonly Price $price,
        public readonly float $rating,
        public readonly bool $active,
        public readonly ?\DateTimeInterface $createdAt = null,
    ) {
    }
}
