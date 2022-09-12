<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata;

use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Repository\ConstructorRepositoryInterface;

/** @internal */
class ConstructorFetcher
{
    public function __construct(
        private readonly ConstructorFactory $constructorFactory,
        private readonly ConstructorRepositoryInterface $constructorRepository,
    ) {
    }

    public function fetch(string $classname): Constructor
    {
        if (null === $constructor = $this->constructorRepository->get($classname)) {
            $this->constructorRepository->add($constructor = $this->constructorFactory->create($classname));
        }

        return $constructor;
    }
}
