<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata;

use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Repository\ConstructorRepositoryInterface;

class ConstructorFetcher
{
    public function __construct(
        private readonly ConstructorFactory $constructorFactory,
        private readonly ConstructorRepositoryInterface $constructorRepository,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @return Constructor<T>
     */
    public function __invoke(string $classname): Constructor
    {
        if (!$constructor = $this->constructorRepository->get($classname)) {
            $this->constructorRepository->add($constructor = $this->constructorFactory->__invoke($classname));
        }

        return $constructor;
    }
}
