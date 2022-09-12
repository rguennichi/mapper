<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

interface MapperInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $target
     *
     * @return T
     */
    public function map(mixed $source, string $target): object;
}
