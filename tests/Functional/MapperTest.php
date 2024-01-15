<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Functional;

use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\MapperInterface;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Repository\InMemoryConstructorRepository;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\Image;
use Tests\Guennichi\Mapper\Fixture\Offer;
use Tests\Guennichi\Mapper\Fixture\OfferList;
use Tests\Guennichi\Mapper\Fixture\Price;
use Tests\Guennichi\Mapper\Fixture\Product;
use Tests\Guennichi\Mapper\Fixture\ProductTypeEnum;

class MapperTest extends TestCase
{
    private MapperInterface $mapper;

    protected function setUp(): void
    {
        $this->mapper = new Mapper(
            new ConstructorFetcher(
                new ConstructorFactory(
                    new ArgumentFactory(
                        new ArgumentTypeFactory(
                            new PhpDocumentorArgumentTypeFactory(),
                            new ReflectionArgumentTypeFactory(),
                        ),
                    ),
                ),
                new InMemoryConstructorRepository(),
            ),
        );
    }

    public function testItMapArrayToObject(): void
    {
        $expectedObject = new Product(
            'Product Custom Name',
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
                    new Price(0),
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
            ProductTypeEnum::Type1,
        );

        self::assertEquals($expectedObject, $this->mapper->__invoke(Product::getMock(), Product::class));
    }

    public function testItMapArrayToCollection(): void
    {
        $expectedCollection = new OfferList(
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
                new Price(0),
                3,
                false,
                new \DateTimeImmutable('2023-02-13 15:30:09.000000', new \DateTimeZone('+02:00')),
            ),
        );

        self::assertEquals($expectedCollection, $this->mapper->__invoke(OfferList::getMock(), OfferList::class));
    }
}
