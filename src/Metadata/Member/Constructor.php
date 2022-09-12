<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Member;

use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\ObjectType;

class Constructor
{
    /**
     * @var array<string, Parameter>
     */
    public readonly array $requiredParameters;

    /**
     * @param array<string, Parameter> $parameters indexed by name
     */
    public function __construct(
        public readonly string $classname,
        public readonly CollectionType|ObjectType $classType,
        public readonly array $parameters,
    ) {
        $this->requiredParameters = array_filter($this->parameters, static fn (Parameter $parameter) => $parameter->required);
    }
}
