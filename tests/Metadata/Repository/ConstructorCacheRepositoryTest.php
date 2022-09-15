<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Repository\ConstructorCacheRepository;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\Attribute\Internal;
use Tests\Guennichi\Mapper\Fixture\Something;
use Tests\Guennichi\Mapper\Fixture\SomethingCollection;

class ConstructorCacheRepositoryTest extends TestCase
{
    private ConstructorCacheRepository $cacheRepository;
    private string $cacheDirectory = __DIR__ . '/../../../cache/test';

    protected function setUp(): void
    {
        $this->cacheRepository = new ConstructorCacheRepository($this->cacheDirectory);
    }

    public function testItCreatesAnEmptyArrayPhpFile(): void
    {
        self::assertTrue(file_exists($this->cacheDirectory . '/' . ConstructorCacheRepository::CACHE_FILENAME));
    }

    public function testItGeneratesValidConstructorInstancePhpCode(): void
    {
        self::assertNull($this->cacheRepository->get(\stdClass::class));

        $expectedConstructor = $this->createConstructor(\stdClass::class);

        $this->cacheRepository->add($expectedConstructor);

        self::assertEquals($expectedConstructor, $this->cacheRepository->get(\stdClass::class));

        // Add same object multiple times
        $this->cacheRepository->add($expectedConstructor);
        $this->cacheRepository->add($expectedConstructor);

        // Ensure it only generates one entry
        self::assertCount(1, require $this->cacheDirectory . '/' . ConstructorCacheRepository::CACHE_FILENAME);
    }

    private function createConstructor(string $classname): Constructor
    {
        return new Constructor(
            $classname,
            new ObjectType(\stdClass::class),
            [
                'param1' => new Parameter(
                    'param1',
                    new CompoundType(
                        new IntegerType(),
                        new ArrayType(
                            new StringType(),
                            new ObjectType(\stdClass::class),
                        ),
                        new BooleanType(),
                    ),
                    true,
                    [],
                ),
                'param2' => new Parameter(
                    'param2',
                    new CollectionType(SomethingCollection::class, new ObjectType(Something::class)),
                    true,
                    [Internal::class => new Internal(), DateTimeFormat::class => new DateTimeFormat(\DATE_RFC1123)],
                ),
            ],
        );
    }

    protected function tearDown(): void
    {
        $this->cacheRepository->clear();
    }
}
