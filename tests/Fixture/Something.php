<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Mapper\Attribute\Flexible;

class Something
{
    /**
     * @param array<string, float> $paramTwo
     * @param int|array<int>|null $paramFive
     */
    public function __construct(
        public readonly string $paramOne,
        public readonly array $paramTwo,
        #[Flexible]
        public readonly bool $paramThree,
        public readonly ?int $paramFour = 0,
        public readonly int|array|null $paramFive = null,
        public readonly ?\DateTimeImmutable $paramSix = null,
    ) {
    }
}
