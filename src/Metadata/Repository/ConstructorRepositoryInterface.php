<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Metadata\Model\Constructor;

interface ConstructorRepositoryInterface
{
    /**
     * @template T of object
     *
     * @param Constructor<T> $constructor
     */
    public function add(Constructor $constructor): void;

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @return Constructor<T>|null
     */
    public function get(string $classname): ?Constructor;

    public function clear(): void;
}
