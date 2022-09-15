<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Metadata\Member\Constructor;

interface ConstructorRepositoryInterface
{
    public function add(Constructor $constructor): void;

    public function get(string $classname): ?Constructor;

    public function clear(): void;
}
