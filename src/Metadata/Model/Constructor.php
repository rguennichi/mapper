<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Model;

use Guennichi\Mapper\Metadata\Type\ObjectType;

/**
 * @template T of object
 */
class Constructor
{
    /**
     * @param class-string<T> $classname
     * @param array<string, Argument> $arguments
     * @param ObjectType<T> $type
     */
    public function __construct(
        public readonly string $classname,
        public readonly array $arguments,
        public readonly ObjectType $type,
    ) {
    }
}
