<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

/**
 * @extends Collection<Offer>
 */
class OfferList extends Collection
{
    public function __construct(Offer ...$offers)
    {
        parent::__construct($offers);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getMock(): array
    {
        return [
            [
                'title' => 'Offer1',
                'image' => ['title' => 'image1', 'url' => 'https://example.com/image1.png'],
                'price' => ['cents' => 500],
                'rating' => 4.3,
                'active' => true,
            ],
            [
                'title' => 'Offer2',
                'image' => ['title' => 'image2', 'url' => 'https://example.com/image2.png'],
                'price' => ['cents' => 800],
                'rating' => 3,
                'active' => false,
                'createdAt' => [
                    'date' => '2023-02-13 15:30:09.000000',
                    'timezone_type' => 1,
                    'timezone' => '+02:00',
                ],
            ],
        ];
    }
}
