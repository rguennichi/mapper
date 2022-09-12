<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use Benchmark\Guennichi\Mapper\Fixture\Head;
use PhpBench\Attributes\BeforeMethods;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[BeforeMethods('setUp')]
class SymfonySerializerBench
{
    private Serializer $serializer;

    public function setUp(): void
    {
        $this->serializer = new Serializer([
            new ObjectNormalizer(null, null, null, new PhpDocExtractor()),
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
        ]);

        $this->serializer->denormalize(Head::INPUT, Head::class);
    }

    public function benchSymfonyDenormalize(): void
    {
        $this->serializer->denormalize(Head::INPUT, Head::class);
    }
}
