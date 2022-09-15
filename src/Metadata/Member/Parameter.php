<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Member;

use Guennichi\Mapper\Metadata\Type\TypeInterface;

class Parameter
{
    /**
     * @template T of \Guennichi\Mapper\Attribute\Attribute
     *
     * @param array<class-string<T>, T> $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly TypeInterface $type,
        public readonly bool $required,
        public readonly array $attributes,
    ) {
    }
}
