<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Repository;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Attribute\Name;
use Guennichi\Mapper\Attribute\Trusted;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Repository\PhpCacheFileConstructorRepository;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BackedEnumType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\IntegerEnum;
use Tests\Guennichi\Mapper\Fixture\StringEnum;

class PhpCacheFileConstructorRepositoryTest extends TestCase
{
    private PhpCacheFileConstructorRepository $repository;
    private string $cacheFile = __DIR__ . '/mapper_metadata.php';

    protected function setUp(): void
    {
        $this->repository = new PhpCacheFileConstructorRepository(\dirname($this->cacheFile));
        $this->repository->clear();
    }

    public function testItCreatesPhpFileWithEmptyArray(): void
    {
        (new PhpCacheFileConstructorRepository(\dirname($this->cacheFile)))->clear();

        self::assertFileExists($this->cacheFile);
        self::assertSame([], require $this->cacheFile);
    }

    public function testItGenerateConstructorClosure(): void
    {
        $constructor = new Constructor(\stdClass::class, [
            'arg1' => new Argument(
                'arg1',
                new CompoundType(
                    [
                        new IntegerType(),
                        new ArrayType(
                            new StringType(),
                            new ObjectType(\stdClass::class),
                        ),
                        new BooleanType(),
                        new BackedEnumType(IntegerEnum::class, new IntegerType()),
                        new BackedEnumType(StringEnum::class, new StringType()),
                    ],
                ),
                false,
                false,
                false,
                false,
                [],
            ),
            'arg2' => new Argument(
                'arg2',
                new CollectionType(\stdClass::class, new ObjectType(\stdClass::class)),
                true,
                false,
                true,
                true,
                [
                    DateTimeFormat::class => new DateTimeFormat(\DATE_RFC1123),
                    Flexible::class => new Flexible(),
                    Name::class => new Name('arg2'),
                    Trusted::class => new Trusted(),
                ],
            ),
        ], new ObjectType(\stdClass::class));

        $repositoryRead = new PhpCacheFileConstructorRepository(\dirname($this->cacheFile));
        $repositoryWrite = new PhpCacheFileConstructorRepository(\dirname($this->cacheFile));
        $repositoryWrite->clear();

        $repositoryWrite->add($constructor);
        $repositoryWrite->add($constructor);

        $precompiledConstructors = require $this->cacheFile;

        self::assertCount(1, $precompiledConstructors);
        self::assertEquals($constructor, $precompiledConstructors[\stdClass::class]());
        self::assertNotSame($constructor, $precompiledConstructors[\stdClass::class]()); // To make sure it's not in-memory caching.
        self::assertSame($constructor, $repositoryWrite->get(\stdClass::class)); // To make sure it's fetching it from memory cache

        self::assertEquals($constructor, $repositoryRead->get(\stdClass::class));
        // To make sure it's not fetching it from memory cache as the readRepository will generate a new reference
        // As it's not aware about the constructor added with writeRepository, so this repository will invoke the callback from cache file
        self::assertNotSame($constructor, $repositoryRead->get(\stdClass::class));

        $repositoryRead->clear();

        $precompiledConstructors = require $this->cacheFile;
        self::assertFileExists($this->cacheFile);
        self::assertEmpty($precompiledConstructors);
        self::assertNull($repositoryRead->get(\stdClass::class));
    }

    public function testItReturnsNullIfNotFound(): void
    {
        self::assertNull($this->repository->get(\stdClass::class));
    }

    protected function tearDown(): void
    {
        unlink($this->cacheFile);
    }
}
