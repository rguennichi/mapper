<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

interface MapperInterface
{
    /**
     * @template T of object
     *
     * @param array<array-key, mixed> $input
     * @param class-string<T> $target
     *
     * @return T
     */
    public function __invoke(array $input, string $target): object;
}
