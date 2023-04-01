<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use PhpBench\Attributes\BeforeMethods;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tests\Guennichi\Mapper\Fixture\Product;

class SymfonySerializerBench
{
    private Serializer $serializer;

    public function setUp(): void
    {
        $this->serializer = new Serializer([
            new ObjectNormalizer(null, null, null, new PhpDocExtractor()),
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
        ], [new JsonEncoder()]);

        $this->serializer->denormalize(Product::getMock(), Product::class);
    }

    #[BeforeMethods('setUp')]
    public function benchSymfonyDenormalize(): void
    {
        $this->serializer->denormalize(Product::getMock(), Product::class);
    }
}
