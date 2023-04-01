<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Repository;

use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Repository\InMemoryConstructorRepository;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use PHPUnit\Framework\TestCase;

class InMemoryConstructorRepositoryTest extends TestCase
{
    private InMemoryConstructorRepository $inMemoryConstructorRepository;

    protected function setUp(): void
    {
        $this->inMemoryConstructorRepository = new InMemoryConstructorRepository();
    }

    public function testItAddsConstructorOnlyOnce(): void
    {
        $constructor = new Constructor(\stdClass::class, [], $this->createMock(ObjectType::class));

        $this->inMemoryConstructorRepository->add($constructor);

        self::assertSame($constructor, $this->inMemoryConstructorRepository->get(\stdClass::class));
    }

    public function testItReturnsNullIfNoConstructorFound(): void
    {
        self::assertNull($this->inMemoryConstructorRepository->get(\stdClass::class));
    }
}
