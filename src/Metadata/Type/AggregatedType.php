<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

abstract class AggregatedType extends Type
{
    /**
     * @param array<TypeInterface> $types
     */
    public function __construct(public readonly array $types, public readonly string $token)
    {
    }

    public function __toString()
    {
        return implode($this->token, array_map(strval(...), $this->types));
    }
}
