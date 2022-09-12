<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Metadata\Member\Constructor;

class ConstructorInMemoryRepository implements ConstructorRepositoryInterface
{
    /** @var array<string, Constructor> */
    private array $constructors = [];

    public function add(Constructor $constructor): void
    {
        if (isset($this->constructors[$constructor->classname])) {
            return;
        }

        $this->constructors[$constructor->classname] = $constructor;
    }

    public function get(string $classname): ?Constructor
    {
        return $this->constructors[$classname] ?? null;
    }
}
