<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use PhpBench\Attributes\BeforeMethods;
use Tests\Guennichi\Mapper\Fixture\Image;
use Tests\Guennichi\Mapper\Fixture\Offer;
use Tests\Guennichi\Mapper\Fixture\OfferList;
use Tests\Guennichi\Mapper\Fixture\Price;
use Tests\Guennichi\Mapper\Fixture\Product;

class PhpUnserializeBench
{
    private string $serializedObject;

    public function setUp(): void
    {
        $this->serializedObject = serialize(
            new Product(
                'Product',
                'Description',
                [
                    new Image('image1', 'https://example.com/image1.png'),
                    new Image('image2', 'https://example.com/image2.png'),
                    new Image('image3', 'https://example.com/image3.png'),
                    new Image('image4', 'https://example.com/image4.png'),
                ],
                new OfferList(
                    new Offer(
                        'Offer1',
                        new Image('image1', 'https://example.com/image1.png'),
                        new Price(500),
                        4.3,
                        true,
                    ),
                    new Offer(
                        'Offer2',
                        new Image('image2', 'https://example.com/image2.png'),
                        new Price(800),
                        3,
                        false,
                        new \DateTimeImmutable('2023-02-13 15:30:09.000000', new \DateTimeZone('+02:00')),
                    ),
                ),
                false,
                true,
                ['choice', 'green'],
                /* @phpstan-ignore-next-line */
                \DateTimeImmutable::createFromFormat('Y-m-d', '2023-02-10'),
                /* @phpstan-ignore-next-line */
                \DateTime::createFromFormat(\DATE_ATOM, '2023-03-01T15:30:09+02:00'),
                /* @phpstan-ignore-next-line */
                \DateTimeImmutable::createFromFormat('m-Y H:i', '03-2023 15:43'),
            ),
        );
    }

    #[BeforeMethods('setUp')]
    public function benchPhpUnserialize(): void
    {
        unserialize($this->serializedObject);
    }
}
