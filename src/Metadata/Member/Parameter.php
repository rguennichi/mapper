<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Member;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Metadata\Type\TypeInterface;

/** @internal */
class Parameter
{
    public readonly bool $flexible;

    /**
     * @param array<class-string<Attribute>, Attribute> $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly TypeInterface $type,
        public readonly bool $required,
        public readonly array $attributes,
    ) {
        $this->flexible = isset($this->attributes[Flexible::class]);
    }
}
