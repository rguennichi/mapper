<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata;

use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Repository\ConstructorRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConstructorFetcherTest extends TestCase
{
    private ConstructorFetcher $constructorFetcher;
    private ConstructorRepositoryInterface&MockObject $constructorRepository;
    private ConstructorFactory&MockObject $constructorFactory;

    protected function setUp(): void
    {
        $this->constructorFetcher = new ConstructorFetcher(
            $this->constructorFactory = $this->createMock(ConstructorFactory::class),
            $this->constructorRepository = $this->createMock(ConstructorRepositoryInterface::class),
        );
    }

    public function testItFetchesConstructorObjectFromRepositoryIfExists(): void
    {
        $constructor = $this->createMock(Constructor::class);

        $this->constructorRepository->expects($this->once())
            ->method('get')
            ->with(\stdClass::class)
            ->willReturn($constructor);

        $this->constructorFactory->expects($this->never())
            ->method('__invoke');

        self::assertSame($constructor, $this->constructorFetcher->__invoke(\stdClass::class));
    }

    public function testItCreatesConstructorObjectFromFactoryAndAddItToRepositoryIfDoesNotExists(): void
    {
        $constructor = $this->createMock(Constructor::class);

        $this->constructorRepository->expects($this->once())
            ->method('get')
            ->with(\stdClass::class)
            ->willReturn(null);

        $this->constructorFactory->expects($this->once())
            ->method('__invoke')
            ->with(\stdClass::class)
            ->willReturn($constructor);

        $this->constructorRepository->expects($this->once())
            ->method('add')
            ->with($constructor);

        self::assertSame($constructor, $this->constructorFetcher->__invoke(\stdClass::class));
    }
}
