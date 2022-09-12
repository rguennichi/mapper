<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper;

use Benchmark\Guennichi\Mapper\Fixture\Child;
use Benchmark\Guennichi\Mapper\Fixture\Head;
use Doctrine\Common\Annotations\AnnotationReader;
use Liip\MetadataParser\Builder;
use Liip\MetadataParser\ModelParser\LiipMetadataAnnotationParser;
use Liip\MetadataParser\ModelParser\PhpDocParser;
use Liip\MetadataParser\ModelParser\ReflectionParser;
use Liip\MetadataParser\Parser;
use Liip\MetadataParser\RecursionChecker;
use Liip\Serializer\Configuration\GeneratorConfiguration;
use Liip\Serializer\DeserializerGenerator;
use Liip\Serializer\Serializer;
use Liip\Serializer\SerializerGenerator;
use Liip\Serializer\SerializerInterface;
use Liip\Serializer\Template\Deserialization;
use Liip\Serializer\Template\Serialization;
use PhpBench\Attributes\Skip;

class LiipSerializerBench
{
    private readonly SerializerInterface $serializer;

    public function __construct()
    {
        $configuration = GeneratorConfiguration::createFomArray([
            'classes' => [
                Head::class => [],
                Child::class => [],
            ],
        ]);

        $parsers = [
            new ReflectionParser(),
            new PhpDocParser(),
            new LiipMetadataAnnotationParser(new AnnotationReader()),
        ];
        $builder = new Builder(new Parser($parsers), new RecursionChecker(null, []));
        $serializerGenerator = new SerializerGenerator(
            new Serialization(),
            $configuration,
            __DIR__ . '/../cache',
        );
        $deserializerGenerator = new DeserializerGenerator(new Deserialization(), [Head::class, Child::class], __DIR__ . '/../cache');

        $serializerGenerator->generate($builder);
        $deserializerGenerator->generate($builder);

        $this->serializer = new Serializer(__DIR__ . '/../cache');
    }

    /**
     * UNFORTUNATELY: THIS WILL NOT WORK DUE TO IMPLEMENTATION ISSUES WHEN TRYING TO DESERIALIZE FROM CONSTRUCTOR.
     * "We currently do not support deserializing when the root class has a non-empty constructor".
     *
     * @see DeserializerGenerator::writeFile()
     */
    #[Skip]
    public function benchLiipDeserialize(): void
    {
        $this->serializer->fromArray(Head::INPUT, Head::class);
    }
}
