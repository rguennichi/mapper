<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Attribute\Name;
use Guennichi\Mapper\Attribute\Trusted;

#[Trusted]
class Product
{
    /**
     * @param array<Image> $images
     * @param array<string> $badges
     */
    public function __construct(
        #[Name('productName')]
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $images,
        public readonly OfferList $offers,
        #[Flexible]
        public readonly bool $marketplace,
        #[Flexible]
        public readonly bool $bestSeller,
        public readonly array $badges,
        #[DateTimeFormat('Y-m-d')]
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTime $updatedAt,
        #[DateTimeFormat('m-Y H:i')]
        public readonly \DateTimeImmutable $enabledAt,
        public readonly ProductTypeEnum $type,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getMock(): array
    {
        return [
            'name' => 'Product',
            'productName' => 'Product Custom Name',
            'description' => 'Description',
            'images' => [
                ['title' => 'image1', 'url' => 'https://example.com/image1.png'],
                ['title' => 'image2', 'url' => 'https://example.com/image2.png'],
                ['title' => 'image3', 'url' => 'https://example.com/image3.png'],
                new Image('image4', 'https://example.com/image4.png'),
            ],
            'offers' => OfferList::getMock(),
            'marketplace' => 'no',
            'bestSeller' => 'yes',
            'badges' => ['choice', 'green'],
            'createdAt' => '2023-02-10',
            'updatedAt' => '2023-03-01T15:30:09+02:00',
            'enabledAt' => '03-2023 15:43',
            'type' => 'type1',
        ];
    }
}
