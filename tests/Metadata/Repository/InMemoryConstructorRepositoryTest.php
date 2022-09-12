<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Repository\ConstructorInMemoryRepository;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use PHPUnit\Framework\TestCase;
use stdClass;

class InMemoryConstructorRepositoryTest extends TestCase
{
    private ConstructorInMemoryRepository $inMemoryConstructorRepository;

    protected function setUp(): void
    {
        $this->inMemoryConstructorRepository = new ConstructorInMemoryRepository();
    }

    public function testItAddsConstructorOnlyOnce(): void
    {
        $constructor = new Constructor(stdClass::class, $this->createMock(ObjectType::class), []);

        $this->inMemoryConstructorRepository->add($constructor);

        self::assertSame($constructor, $this->inMemoryConstructorRepository->get(stdClass::class));
    }

    public function testItReturnsNullIfNoConstructorFound(): void
    {
        self::assertNull($this->inMemoryConstructorRepository->get(stdClass::class));
    }
}
