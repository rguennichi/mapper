<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Model;

use Guennichi\Mapper\Attribute\Name;
use Guennichi\Mapper\Metadata\Type\TypeInterface;

class Argument
{
    /**
     * @template T of object
     *
     * @param array<class-string<T>, T> $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly TypeInterface $type,
        public readonly bool $optional,
        public readonly bool $variadic,
        public readonly bool $trusted,
        public readonly bool $flexible,
        public readonly array $attributes,
    ) {
    }

    public function getAttribute(string $name): ?object
    {
        return $this->attributes[$name] ?? null;
    }

    public function getMappedName(): string
    {
        return $this->getAttribute(Name::class)?->name ?? $this->name;
    }
}
