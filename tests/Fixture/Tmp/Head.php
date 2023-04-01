<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture\Tmp;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Attribute\Flexible;

class Head
{
    public const INPUT = [
        'paramOne' => 'test1',
        'paramTwo' => [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ],
        'paramThree' => 'true',
        'paramFour' => 60,
        'paramFive' => [10, 50, 300],
        'paramSix' => [
            [
                'paramOne' => 'child1',
                'paramTwo' => 123.55,
            ],
            [
                'paramOne' => 'child2',
                'paramTwo' => 666.,
            ],
            [
                'paramOne' => 'child3',
                'paramTwo' => 8060.99,
            ],
        ],
        'paramSeven' => '2022-06-10',
    ];

    /**
     * @param array<string, string|null> $paramTwo
     * @param array<int> $paramFive
     * @param array<Child> $paramSix
     */
    public function __construct(
        public readonly string $paramOne,
        #[Flexible]
        public readonly array $paramTwo,
        #[Flexible]
        public readonly bool $paramThree,
        public readonly ?int $paramFour = 0,
        #[Flexible]
        public readonly array $paramFive = [],
        public readonly array $paramSix = [],
        #[DateTimeFormat('Y-m-d')]
        public readonly ?\DateTimeImmutable $paramSeven = null,
    ) {
    }
}
